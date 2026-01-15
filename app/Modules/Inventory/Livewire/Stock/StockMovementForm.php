<?php

namespace App\Modules\Inventory\Livewire\Stock;

use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\StockService;
use Livewire\Component;

class StockMovementForm extends Component
{
    public $product_id;
    public $warehouse_id;
    public $to_warehouse_id; // For transfer
    public $type = 'in'; // in, out, transfer, adjustment
    public $quantity;
    public $notes;
    public $current_stock = 0; // For adjustment display

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'warehouse_id' => 'required|exists:warehouses,id',
        'type' => 'required|in:in,out,transfer,adjustment',
        'quantity' => 'required|numeric|min:0.001',
        'notes' => 'nullable|string',
    ];

    public function mount()
    {
        // Set default warehouse logic if needed
        // $this->warehouse_id = Warehouse::where('is_default', true)->first()->id ?? null;
    }

    public function closeModal()
    {
        $this->reset(['product_id', 'warehouse_id', 'to_warehouse_id', 'quantity', 'notes', 'current_stock']);
        $this->type = 'in';
        $this->dispatch('close-modal');
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['product_id', 'warehouse_id'])) {
            $this->updateCurrentStock();
        }
    }

    public function updateCurrentStock()
    {
        if ($this->product_id && $this->warehouse_id && $this->type === 'adjustment') {
            $service = app(StockService::class);
            $this->current_stock = $service->getAvailableStock($this->product_id, $this->warehouse_id);
            // Note: adjustment might take new quantity (absolute) or diff. Service takes new absolute quantity.
            // If this form takes absolute quantity for adjustment, then we need to know current.
            // If form takes diff, then it's like in/out.
            // Prompt says: "Pour ajustement : affiche stock actuel, saisie nouveau stock"
            // So we input new absolute quantity.

            if (!$this->quantity) {
                $this->quantity = $this->current_stock;
            }
        }
    }

    public function save(StockService $service)
    {
        $this->validate();

        if ($this->type === 'transfer') {
            $this->validate(['to_warehouse_id' => 'required|exists:warehouses,id|different:warehouse_id']);
        }

        try {
            switch ($this->type) {
                case 'in':
                    $service->addStock($this->product_id, $this->warehouse_id, $this->quantity, $this->notes);
                    break;
                case 'out':
                    $service->removeStock($this->product_id, $this->warehouse_id, $this->quantity, $this->notes);
                    break;
                case 'transfer':
                    $service->transfer($this->product_id, $this->warehouse_id, $this->to_warehouse_id, $this->quantity, $this->notes);
                    break;
                case 'adjustment':
                    $service->adjust($this->product_id, $this->warehouse_id, $this->quantity, $this->notes);
                    break;
            }

            $this->closeModal();
            $this->dispatch('refresh-stock-dashboard'); // Assuming dashboard listens
            $this->dispatch('notify', message: 'Mouvement enregistré avec succès', type: 'success');

        } catch (\Exception $e) {
            $this->addError('quantity', $e->getMessage());
        }
    }

    public function getProductsProperty()
    {
        return Product::where('is_active', true)->get();
    }

    public function getWarehousesProperty()
    {
        return Warehouse::all();
    }

    public function render()
    {
        return view('livewire.inventory.stock.stock-movement-form', [
            'products' => $this->products,
            'warehouses' => $this->warehouses,
        ]);
    }
}
