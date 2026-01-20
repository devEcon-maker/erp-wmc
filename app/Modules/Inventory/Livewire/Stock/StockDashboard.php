<?php

namespace App\Modules\Inventory\Livewire\Stock;

use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Models\StockLevel;
use App\Modules\Inventory\Models\StockMovement;
use Livewire\Component;
use Livewire\WithPagination;

class StockDashboard extends Component
{
    use WithPagination;

    public $warehouseId;
    public $search = '';
    public $showAlertsOnly = false;
    public $category = '';

    protected $queryString = [
        'warehouseId' => ['except' => ''],
        'search' => ['except' => ''],
        'showAlertsOnly' => ['except' => false],
    ];

    public function mount()
    {
        $defaultWarehouse = Warehouse::where('is_default', true)->first();
        $this->warehouseId = $defaultWarehouse ? $defaultWarehouse->id : Warehouse::first()->id ?? null;
    }

    public function getStatsProperty()
    {
        // Compter les produits en alerte (utiliser current_stock calculé)
        $products = Product::where('is_active', true)
            ->where('track_stock', true)
            ->get();

        $productsInAlert = $products->filter(function ($product) {
            return ($product->current_stock ?? 0) <= ($product->min_stock_alert ?? 0);
        })->count();

        $movementsToday = StockMovement::whereDate('created_at', today())
            ->when($this->warehouseId, fn($q) => $q->where('warehouse_id', $this->warehouseId))
            ->count();

        return [
            'value' => 0, // Placeholder
            'alerts' => $productsInAlert,
            'movements' => $movementsToday,
        ];
    }

    public function render()
    {
        $warehouses = Warehouse::all();

        $products = Product::query()
            ->with([
                'category',
                'stockLevels' => function ($q) {
                    if ($this->warehouseId)
                        $q->where('warehouse_id', $this->warehouseId);
                }
            ])
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')->orWhere('reference', 'like', '%' . $this->search . '%'))
            ->when($this->showAlertsOnly, function ($q) {
                $q->whereHas('stockLevels', function ($sq) {
                    if ($this->warehouseId)
                        $sq->where('warehouse_id', $this->warehouseId);
                    $sq->whereColumn('quantity', '<=', 'products.min_stock_alert');
                });
            })
            ->paginate(20);

        return view('livewire.inventory.stock.stock-dashboard', [
            'warehouses' => $warehouses,
            'products' => $products,
            'stats' => $this->stats,
        ])->layout('layouts.app');
    }
}
