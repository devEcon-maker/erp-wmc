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
        // Global stats across all warehouses or selected one? Prompt says "Overview".
        // Let's filter by selected warehouse if possible, or global?
        // Usually dashboards are global or per context. Let's do selected warehouse context.

        $query = StockLevel::query();
        if ($this->warehouseId) {
            $query->where('warehouse_id', $this->warehouseId);
        }

        $totalValue = 0; // Need product cost/price.
        // Assuming we calculate value based on current quantity * purchase_price
        // This is heavy. Let's do approximate or simpler.
        // Or aggregate from DB.

        $productsInAlert = Product::where('is_active', true)
            ->whereHas('stockLevels', function ($q) {
                if ($this->warehouseId)
                    $q->where('warehouse_id', $this->warehouseId)->whereRaw('quantity <= products.min_stock_alert');
                else
                    $q->whereRaw('quantity <= products.min_stock_alert');
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
