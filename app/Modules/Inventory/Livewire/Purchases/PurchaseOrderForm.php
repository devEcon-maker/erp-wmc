<?php

namespace App\Modules\Inventory\Livewire\Purchases;

use App\Modules\Inventory\Models\PurchaseOrder;
use App\Modules\Inventory\Models\PurchaseOrderLine;
use App\Modules\Inventory\Models\Product;
use App\Modules\CRM\Models\Contact;
use App\Models\User;
use App\Modules\Inventory\Services\PurchaseOrderService;
use Livewire\Component;

class PurchaseOrderForm extends Component
{
    public ?PurchaseOrder $purchaseOrder = null;
    public $supplier_id;
    public $assigned_to;
    public $date;
    public $expected_date;
    public $notes;

    public $lines = [];

    protected $rules = [
        'supplier_id' => 'required|exists:contacts,id',
        'assigned_to' => 'required|exists:users,id',
        'date' => 'required|date',
        'expected_date' => 'nullable|date|after_or_equal:date',
        'lines.*.product_id' => 'required|exists:products,id',
        'lines.*.quantity' => 'required|numeric|min:0.001',
        'lines.*.unit_price' => 'required|numeric|min:0',
    ];

    public function mount(PurchaseOrder $purchaseOrder = null)
    {
        if ($purchaseOrder && $purchaseOrder->exists) {
            $this->purchaseOrder = $purchaseOrder;
            $this->supplier_id = $purchaseOrder->supplier_id;
            $this->assigned_to = $purchaseOrder->assigned_to;
            $this->date = $purchaseOrder->date->format('Y-m-d');
            $this->expected_date = $purchaseOrder->expected_date?->format('Y-m-d');
            $this->notes = $purchaseOrder->notes;

            foreach ($purchaseOrder->lines as $line) {
                $this->lines[] = [
                    'id' => $line->id,
                    'product_id' => $line->product_id,
                    'description' => $line->description,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unit_price,
                    'tax_rate' => $line->tax_rate,
                    'total' => $line->quantity * $line->unit_price
                ];
            }
        } else {
            $this->date = date('Y-m-d');
            $this->addLine();
        }
    }

    public function addLine()
    {
        $this->lines[] = [
            'id' => null,
            'product_id' => '',
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'tax_rate' => 20.00,
            'total' => 0
        ];
    }

    public function removeLine($index)
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function updatedLines($value, $key)
    {
        // $key format: 0.product_id
        $parts = explode('.', $key);
        $index = $parts[0];
        $field = $parts[1];

        if ($field === 'product_id') {
            $product = Product::find($value);
            if ($product) {
                $this->lines[$index]['description'] = $product->name;
                $this->lines[$index]['unit_price'] = $product->purchase_price; // Assuming field exists
                $this->lines[$index]['tax_rate'] = $product->tax_rate;
            }
        }

        // Recalculate line total
        if (in_array($field, ['quantity', 'unit_price'])) {
            $qty = floatval($this->lines[$index]['quantity']);
            $price = floatval($this->lines[$index]['unit_price']);
            $this->lines[$index]['total'] = $qty * $price;
        }
    }

    public function save(PurchaseOrderService $service)
    {
        $this->validate();

        if ($this->purchaseOrder && $this->purchaseOrder->exists) {
            $po = $this->purchaseOrder;
            $po->update([
                'supplier_id' => $this->supplier_id,
                'assigned_to' => $this->assigned_to,
                'date' => $this->date,
                'expected_date' => $this->expected_date,
                'notes' => $this->notes,
            ]);

            // Sync lines: delete old, create new (simpler) or update
            // For simplicity in MVP: delete all lines and recreate
            $po->lines()->delete();
        } else {
            // Generate reference
            $po = new PurchaseOrder();
            $po->supplier_id = $this->supplier_id;
            $po->assigned_to = $this->assigned_to;
            // Generate Reference logic here or in service?
            // Let's do a simple one: ACH-YYYYMM-TIMESTAMP for now or implement generateReference in Service
            $po->reference = 'ACH-' . date('Ym') . '-' . time(); // Temporary
            $po->date = $this->date;
            $po->expected_date = $this->expected_date;
            $po->notes = $this->notes;
            $po->status = 'draft';
            $po->save();
        }

        foreach ($this->lines as $line) {
            $po->lines()->create([
                'product_id' => $line['product_id'],
                'description' => $line['description'] ?: 'Product',
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'tax_rate' => $line['tax_rate'],
            ]);
        }

        $service->calculateTotals($po);

        return redirect()->route('inventory.purchases.show', $po);
    }

    public function render()
    {
        // Utilisateurs habilités à valider les achats (comptable, direction, admin)
        $approvers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'comptable', 'direction', 'manager']);
        })->orWhereHas('permissions', function ($q) {
            $q->where('name', 'purchases.approve');
        })->orderBy('name')->get();

        // Si aucun approbateur trouvé, afficher tous les utilisateurs
        if ($approvers->isEmpty()) {
            $approvers = User::orderBy('name')->get();
        }

        return view('livewire.inventory.purchases.purchase-order-form', [
            'suppliers' => Contact::where('type', 'fournisseur')->orderBy('company_name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
            'approvers' => $approvers,
        ])->layout('layouts.app');
    }
}
