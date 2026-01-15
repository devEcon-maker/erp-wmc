<?php

namespace App\Modules\CRM\Livewire;

use Livewire\Component;
use App\Modules\CRM\Models\Proposal;
use App\Modules\CRM\Models\ProposalLine;
use App\Modules\CRM\Models\Contact;
use App\Modules\CRM\Services\ProposalService;
use App\Modules\Inventory\Models\Product;
use Illuminate\Support\Facades\DB;

class ProposalForm extends Component
{
    public Proposal $proposal;
    public $lines = [];
    public $contacts;
    public $products;

    // Form fields
    public $contact_id;
    public $valid_until;
    public $notes;
    public $terms;

    // Totals match Service calculation
    public $total_amount = 0;
    public $tax_amount = 0;
    public $discount_amount = 0;
    public $total_amount_ttc = 0;

    protected $rules = [
        'contact_id' => 'required|exists:contacts,id',
        'valid_until' => 'nullable|date',
        'lines.*.description' => 'required|string',
        'lines.*.quantity' => 'required|numeric|min:1',
        'lines.*.unit_price' => 'required|numeric|min:0',
        'lines.*.tax_rate' => 'required|numeric|min:0',
        'lines.*.discount_rate' => 'required|numeric|min:0|max:100',
    ];

    protected $messages = [
        'contact_id.required' => 'Veuillez sélectionner un client.',
        'lines.*.description.required' => 'La description de la ligne est obligatoire.',
        'lines.*.quantity.required' => 'La quantité est obligatoire.',
        'lines.*.quantity.min' => 'La quantité doit être au moins 1.',
        'lines.*.unit_price.required' => 'Le prix unitaire est obligatoire.',
        'lines.*.unit_price.min' => 'Le prix unitaire ne peut pas être négatif.',
    ];

    public function mount(Proposal $proposal = null)
    {
        $this->contacts = Contact::all();
        $this->products = Product::all(); // Assuming Inventory module exists or stubbed

        if ($proposal && $proposal->exists) {
            $this->proposal = $proposal;
            $this->contact_id = $proposal->contact_id;
            $this->valid_until = $proposal->valid_until?->format('Y-m-d');
            $this->notes = $proposal->notes;
            $this->terms = $proposal->terms;

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
        } else {
            $this->proposal = new Proposal();
            $this->addLine(); // Start with one empty line
        }

        $this->calculateTotals();
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

            // If product selected, auto-fill details
            if (str_ends_with($key, 'product_id')) {
                $productId = $this->lines[$index]['product_id'];
                $product = $this->products->find($productId);
                if ($product) {
                    $this->lines[$index]['description'] = $product->name; // or description
                    $this->lines[$index]['unit_price'] = $product->selling_price; // Assuming field
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

    public function save(ProposalService $service)
    {
        $this->validate();

        $isEdit = $this->proposal->exists;

        DB::transaction(function () use ($service) {
            if (!$this->proposal->exists) {
                $this->proposal->reference = $service->generateReference();
                $this->proposal->created_by = auth()->id();
            }

            $this->proposal->fill([
                'contact_id' => $this->contact_id,
                'valid_until' => $this->valid_until,
                'notes' => $this->notes,
                'terms' => $this->terms,
            ]);

            $this->proposal->save();

            // Sync lines
            $this->proposal->lines()->delete();
            foreach ($this->lines as $lineData) {
                // Calculate line totals for saving
                $qty = floatval($lineData['quantity']);
                $price = floatval($lineData['unit_price']);
                $discountRate = floatval($lineData['discount_rate']);
                $lineTotal = $qty * $price * (1 - ($discountRate / 100)); // Storing net line total usually

                $this->proposal->lines()->create([
                    'product_id' => $lineData['product_id'],
                    'description' => $lineData['description'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'tax_rate' => $lineData['tax_rate'],
                    'discount_rate' => $lineData['discount_rate'],
                    'total_amount' => $lineTotal,
                ]);
            }

            // Recalculate main totals using service logic to be sure
            $service->calculateTotals($this->proposal);
        });

        $message = $isEdit
            ? "Devis {$this->proposal->reference} mis à jour avec succès."
            : "Devis {$this->proposal->reference} créé avec succès.";

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => $message
        ]);

        session()->flash('success', $message);
        return redirect()->route('crm.proposals.index');
    }

    public function render()
    {
        return view('crm::livewire.proposal-form')->layout('layouts.app');
    }
}
