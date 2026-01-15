<?php

namespace App\Modules\Inventory\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Modules\Inventory\Livewire\Stock\StockDashboard;
use App\Modules\Inventory\Livewire\Stock\StockHistory;
use App\Modules\Inventory\Livewire\Stock\StockMovementForm;
use App\Modules\Inventory\Livewire\ProductsList;
use App\Modules\Inventory\Livewire\ProductForm;
use App\Modules\Inventory\Livewire\ProductShow;

class InventoryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'inventory');

        Livewire::component('inventory.stock.stock-dashboard', StockDashboard::class);
        Livewire::component('inventory.stock.stock-history', StockHistory::class);
        Livewire::component('inventory.stock.stock-movement-form', StockMovementForm::class);

        Livewire::component('inventory.products-list', ProductsList::class);
        Livewire::component('inventory.product-form', ProductForm::class);
        Livewire::component('inventory.product-show', ProductShow::class);
    }

    public function register(): void
    {
        //
    }
}

