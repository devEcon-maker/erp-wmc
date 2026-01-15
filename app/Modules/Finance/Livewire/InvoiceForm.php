<?php

namespace App\Modules\Finance\Livewire;

use Livewire\Component;
use App\Modules\Finance\Models\Invoice;
use App\Modules\CRM\Models\Contact;
use App\Modules\Finance\Services\InvoiceService;
use App\Modules\Inventory\Models\Product;
use Illuminate\Support\Facades\DB;

class InvoiceForm extends Component
{
    public Invoice $invoice;
    public $lines = [];
    public $contacts;
    public $products;

    // Form Fields
    public $contact_id;
    public $reference;
    public $order_date;
    public $due_date;
    public $notes;
    public $terms;

    // Totals
    public $total_amount = 0;
    public $tax_amount = 0;
    public $discount_amount = 0;
    public $total_amount_ttc = 0;

    protected $rules = [
        'contact_id' => 'required|exists:contacts,id',
        'order_date' => 'required|date',
        'due_date' => 'required|date|after_or_equal:order_date',
        'lines.*.description' => 'required|string',
        'lines.*.quantity' => 'required|numeric|min:1',
        'lines.*.unit_price' => 'required|numeric|min:0',
    ];

    public function mount(Invoice $invoice = null)
    {
        $this->contacts = Contact::all();
        $this->products = Product::all();

        if ($invoice && $invoice->exists) {
            $this->invoice = $invoice;
            $this->loadInvoiceData();
        } else {
            $this->invoice = new Invoice();
            $this->order_date = now()->format('Y-m-d');
            $this->due_date = now()->addDays(30)->format('Y-m-d');
            $this->addLine();
        }

        $this->calculateTotals();
    }

    protected function loadInvoiceData()
    {
        $this->contact_id = $this->invoice->contact_id;
        $this->reference = $this->invoice->reference;
        $this->order_date = $this->invoice->order_date->format('Y-m-d');
        $this->due_date = $this->invoice->due_date->format('Y-m-d');
        $this->notes = $this->invoice->notes;
        $this->terms = $this->invoice->terms;

        foreach ($this->invoice->lines as $line) {
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

    public function save(InvoiceService $service)
    {
        $this->validate();

        DB::transaction(function () use ($service) {
            if (!$this->invoice->exists) {
                $this->invoice->reference = $service->generateReference();
                $this->invoice->created_by = auth()->id();
                $this->invoice->status = 'draft';
            }

            $this->invoice->fill([
                'contact_id' => $this->contact_id,
                'order_date' => $this->order_date,
                'due_date' => $this->due_date,
                'notes' => $this->notes,
                'terms' => $this->terms,
            ]);

            $this->invoice->save();

            // Sync lines
            $this->invoice->lines()->delete();
            foreach ($this->lines as $lineData) {
                $qty = floatval($lineData['quantity']);
                $price = floatval($lineData['unit_price']);
                $discountRate = floatval($lineData['discount_rate']);
                $lineTotal = $qty * $price * (1 - ($discountRate / 100));

                $this->invoice->lines()->create([
                    'product_id' => $lineData['product_id'],
                    'description' => $lineData['description'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'tax_rate' => $lineData['tax_rate'],
                    'discount_rate' => $lineData['discount_rate'],
                    'total_amount' => $lineTotal,
                ]);
            }

            $service->calculateTotals($this->invoice);
        });

        session()->flash('success', 'Facture enregistrée avec succès.');
        return redirect()->route('finance.invoices.index');
    }

    public function render()
    {
        return view('finance::livewire.invoice-form')->layout('layouts.app');
    }
}
