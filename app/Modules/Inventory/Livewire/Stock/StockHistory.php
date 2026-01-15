<?php

namespace App\Modules\Inventory\Livewire\Stock;

use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\Warehouse;
use Livewire\Component;
use Livewire\WithPagination;

class StockHistory extends Component
{
    use WithPagination;

    public $warehouseId;
    public $productId;
    public $type;
    public $dateFrom;
    public $dateTo;

    public function render()
    {
        $movements = StockMovement::query()
            ->with(['product', 'warehouse', 'creator', 'reference', 'fromWarehouse'])
            ->when($this->warehouseId, function ($q) {
                // Should show movements where this warehouse is involved (either as primary or from)
                $q->where(function ($sq) {
                    $sq->where('warehouse_id', $this->warehouseId)
                        ->orWhere('from_warehouse_id', $this->warehouseId);
                });
            })
            ->when($this->productId, fn($q) => $q->where('product_id', $this->productId))
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->when($this->dateFrom, fn($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('date', '<=', $this->dateTo))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('livewire.inventory.stock.stock-history', [
            'movements' => $movements,
            'warehouses' => Warehouse::all(),
            'products' => Product::take(100)->get(), // Ideally use autocomplete
        ])->layout('layouts.app');
    }
}
