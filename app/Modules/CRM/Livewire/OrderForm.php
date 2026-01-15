<?php

namespace App\Modules\CRM\Livewire;

use Livewire\Component;
use App\Modules\CRM\Models\Order;
use App\Modules\CRM\Models\Contact;
use App\Modules\CRM\Models\Proposal;
use App\Modules\CRM\Services\OrderService;
use App\Modules\Inventory\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderForm extends Component
{
    public Order $order;
    public $lines = [];
    public $contacts;
    public $products;

    // Form fields
    public $contact_id;
    public $order_date;
    public $delivery_date;
    public $shipping_address;
    public $notes;

    // Totals
    public $total_amount = 0;
    public $tax_amount = 0;
    public $discount_amount = 0;
    public $total_amount_ttc = 0;

    protected $rules = [
        'contact_id' => 'required|exists:contacts,id',
        'order_date' => 'required|date',
        'delivery_date' => 'nullable|date',
        'lines.*.description' => 'required|string',
        'lines.*.quantity' => 'required|numeric|min:1',
        'lines.*.unit_price' => 'required|numeric|min:0',
        'lines.*.tax_rate' => 'required|numeric|min:0',
        'lines.*.discount_rate' => 'required|numeric|min:0|max:100',
    ];

    public function mount(Order $order = null, $proposal_id = null)
    {
        $this->contacts = Contact::all();
        $this->products = Product::all();

        if ($order && $order->exists) {
            $this->order = $order;
            $this->loadOrderData();
        } elseif ($proposal_id) {
            $this->loadFromProposal($proposal_id);
        } else {
            $this->order = new Order();
            $this->order_date = now()->format('Y-m-d');
            $this->addLine();
        }

        $this->calculateTotals();
    }

    protected function loadOrderData()
    {
        $this->contact_id = $this->order->contact_id;
        $this->order_date = $this->order->order_date->format('Y-m-d');
        $this->delivery_date = $this->order->delivery_date?->format('Y-m-d');
        $this->shipping_address = $this->order->shipping_address;
        $this->notes = $this->order->notes;

        foreach ($this->order->lines as $line) {
            $this->lines[] = [
                'product_id' => $line->product_id,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
                'tax_rate' => $line->tax_rate,
                'discount_rate' => $line->discount_rate,
            ];
        }
    }

    protected function loadFromProposal($proposalId)
    {
        $proposal = Proposal::with('lines')->find($proposalId);
        if (!$proposal)
            return;

        $this->order = new Order();
        $this->order->proposal_id = $proposal->id;
        $this->contact_id = $proposal->contact_id;
        $this->order_date = now()->format('Y-m-d');
        $this->notes = $proposal->notes;

        foreach ($proposal->lines as $line) {
            $this->lines[] = [
                'product_id' => $line->product_id,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
                'tax_rate' => $line->tax_rate,
                'discount_rate' => $line->discount_rate,
            ];
        }
    }

    public function addLine()
    {
        $this->lines[] = [
            'product_id' => null,
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'tax_rate' => 18.00,
            'discount_rate' => 0,
        ];
    }

    public function removeLine($index)
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
        $this->calculateTotals();
    }

    public function updatedLines($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) >= 2) {
            $index = $parts[0];
            if (str_ends_with($key, 'product_id')) {
                $productId = $this->lines[$index]['product_id'];
                $product = $this->products->find($productId);
                if ($product) {
                    $this->lines[$index]['description'] = $product->name;
                    $this->lines[$index]['unit_price'] = $product->selling_price;
                }
            }
        }
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->total_amount = 0;
        $this->tax_amount = 0;
        $this->discount_amount = 0;

        foreach ($this->lines as $line) {
            $qty = floatval($line['quantity'] ?? 0);
            $price = floatval($line['unit_price'] ?? 0);
            $discountRate = floatval($line['discount_rate'] ?? 0);
            $taxRate = floatval($line['tax_rate'] ?? 0);

            $lineTotal = $qty * $price;
            $lineDiscount = $lineTotal * ($discountRate / 100);
            $lineTotalAfterDiscount = $lineTotal - $lineDiscount;
            $lineTax = $lineTotalAfterDiscount * ($taxRate / 100);

            $this->total_amount += $lineTotal;
            $this->discount_amount += $lineDiscount;
            $this->tax_amount += $lineTax;
        }

        $this->total_amount_ttc = ($this->total_amount - $this->discount_amount) + $this->tax_amount;
    }

    public function save(OrderService $service)
    {
        $this->validate();

        $isEdit = $this->order->exists;

        DB::transaction(function () use ($service) {
            if (!$this->order->exists) {
                $this->order->reference = $service->generateReference();
                $this->order->created_by = auth()->id();
                $this->order->status = 'draft';
            }

            $this->order->fill([
                'contact_id' => $this->contact_id,
                'order_date' => $this->order_date,
                'delivery_date' => $this->delivery_date,
                'shipping_address' => $this->shipping_address,
                'notes' => $this->notes,
            ]);

            $this->order->save();

            // Sync lines
            $this->order->lines()->delete();
            foreach ($this->lines as $lineData) {
                $qty = floatval($lineData['quantity']);
                $price = floatval($lineData['unit_price']);
                $discountRate = floatval($lineData['discount_rate']);
                $lineTotal = $qty * $price * (1 - ($discountRate / 100));

                $this->order->lines()->create([
                    'product_id' => $lineData['product_id'],
                    'description' => $lineData['description'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'tax_rate' => $lineData['tax_rate'],
                    'discount_rate' => $lineData['discount_rate'],
                    'total_amount' => $lineTotal,
                ]);
            }

            $service->calculateTotals($this->order);
        });

        $message = $isEdit
            ? "Commande {$this->order->reference} mise à jour avec succès."
            : "Commande {$this->order->reference} créée avec succès.";

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => $message
        ]);

        session()->flash('success', $message);
        return redirect()->route('crm.orders.index');
    }

    public function render()
    {
        return view('crm::livewire.order-form')->layout('layouts.app');
    }
}
