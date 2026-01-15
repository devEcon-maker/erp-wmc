<?php

namespace App\Modules\CRM\Livewire;

use Livewire\Component;
use App\Modules\CRM\Models\Contract;
use App\Modules\CRM\Models\Contact;
use App\Modules\CRM\Services\ContractService;
use App\Modules\Inventory\Models\Product;
use Illuminate\Support\Facades\DB;

class ContractForm extends Component
{
    public Contract $contract;
    public $lines = [];
    public $contacts;
    public $products;

    // Form fields
    public $contact_id;
    public $type = 'contract';
    public $status = 'draft';
    public $start_date;
    public $end_date;
    public $billing_frequency = 'once';
    public $notes;
    public $terms;

    // Totals
    public $total_amount = 0;
    public $tax_amount = 0;
    public $discount_amount = 0;
    public $total_amount_ttc = 0;

    protected $rules = [
        'contact_id' => 'required|exists:contacts,id',
        'type' => 'required|in:contract,subscription',
        'start_date' => 'required|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'billing_frequency' => 'required|in:once,monthly,quarterly,yearly',
        'lines.*.description' => 'required|string',
        'lines.*.quantity' => 'required|numeric|min:1',
        'lines.*.unit_price' => 'required|numeric|min:0',
    ];

    public function mount(Contract $contract = null)
    {
        $this->contacts = Contact::all();
        $this->products = Product::all();

        if ($contract && $contract->exists) {
            $this->contract = $contract;
            $this->loadContractData();
        } else {
            $this->contract = new Contract();
            $this->start_date = now()->format('Y-m-d');
            $this->end_date = now()->addYear()->format('Y-m-d');
            $this->addLine();
        }

        $this->calculateTotals();
    }

    protected function loadContractData()
    {
        $this->contact_id = $this->contract->contact_id;
        $this->type = $this->contract->type;
        $this->status = $this->contract->status;
        $this->start_date = $this->contract->start_date->format('Y-m-d');
        $this->end_date = $this->contract->end_date?->format('Y-m-d');
        $this->billing_frequency = $this->contract->billing_frequency;
        $this->notes = $this->contract->notes;
        $this->terms = $this->contract->terms;

        foreach ($this->contract->lines as $line) {
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

    public function updatedType($value)
    {
        if ($value === 'subscription') {
            $this->billing_frequency = 'monthly'; // Default for subscription
        } else {
            $this->billing_frequency = 'once';
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

    public function save(ContractService $service)
    {
        $this->validate();

        $isEdit = $this->contract->exists;

        DB::transaction(function () use ($service) {
            if (!$this->contract->exists) {
                $this->contract->reference = $service->generateReference();
                $this->contract->created_by = auth()->id();
                $this->contract->status = 'draft';
            }

            $this->contract->fill([
                'contact_id' => $this->contact_id,
                'type' => $this->type,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'billing_frequency' => $this->billing_frequency,
                'notes' => $this->notes,
                'terms' => $this->terms,
            ]);

            $this->contract->save();

            // Sync lines
            $this->contract->lines()->delete();
            foreach ($this->lines as $lineData) {
                $qty = floatval($lineData['quantity']);
                $price = floatval($lineData['unit_price']);
                $discountRate = floatval($lineData['discount_rate']);
                $lineTotal = $qty * $price * (1 - ($discountRate / 100));

                $this->contract->lines()->create([
                    'product_id' => $lineData['product_id'],
                    'description' => $lineData['description'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'tax_rate' => $lineData['tax_rate'],
                    'discount_rate' => $lineData['discount_rate'],
                    'total_amount' => $lineTotal,
                ]);
            }

            $service->calculateTotals($this->contract);
        });

        $message = $isEdit
            ? "Contrat {$this->contract->reference} mis à jour avec succès."
            : "Contrat {$this->contract->reference} créé avec succès.";

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => $message
        ]);

        session()->flash('success', $message);
        return redirect()->route('crm.contracts.index');
    }

    public function render()
    {
        return view('crm::livewire.contract-form')->layout('layouts.app');
    }
}
