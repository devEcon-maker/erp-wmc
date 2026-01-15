<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Inventory\Livewire\ProductsList;
use App\Modules\Inventory\Livewire\ProductForm;
use App\Modules\Inventory\Livewire\ProductShow;
use App\Modules\Inventory\Livewire\Stock\StockDashboard;
use App\Modules\Inventory\Livewire\Stock\StockHistory;
use App\Modules\Inventory\Livewire\Purchases\PurchaseOrdersList;
use App\Modules\Inventory\Livewire\Purchases\PurchaseOrderForm;
use App\Modules\Inventory\Livewire\Purchases\PurchaseOrderShow;
use App\Modules\Inventory\Livewire\Purchases\ReorderSuggestions;

Route::middleware(['web', 'auth'])->prefix('inventory')->name('inventory.')->group(function () {
    // === PRODUITS ===
    Route::middleware('permission:products.view')->group(function () {
        Route::get('/products', ProductsList::class)->name('products.index');
    });
    // Create - AVANT /{product}
    Route::middleware('permission:products.create')->group(function () {
        Route::get('/products/create', ProductForm::class)->name('products.create');
    });
    // View - APRES /create
    Route::middleware('permission:products.view')->group(function () {
        Route::get('/products/{product}', ProductShow::class)->name('products.show');
    });
    // Edit
    Route::middleware('permission:products.edit')->group(function () {
        Route::get('/products/{product}/edit', ProductForm::class)->name('products.edit');
    });

    // === STOCK ===
    Route::middleware('permission:stock.view')->group(function () {
        Route::get('/stock', StockDashboard::class)->name('stock.dashboard');
        Route::get('/stock/history', StockHistory::class)->name('stock.history');
    });

    // === ACHATS ===
    Route::middleware('permission:purchases.view')->group(function () {
        Route::get('/purchases', PurchaseOrdersList::class)->name('purchases.index');
        Route::get('/purchases/suggestions', ReorderSuggestions::class)->name('purchases.suggestions');
    });
    // Create - AVANT /{purchaseOrder}
    Route::middleware('permission:purchases.create')->group(function () {
        Route::get('/purchases/create', PurchaseOrderForm::class)->name('purchases.create');
    });
    // View - APRES /create
    Route::middleware('permission:purchases.view')->group(function () {
        Route::get('/purchases/{purchaseOrder}', PurchaseOrderShow::class)->name('purchases.show');
    });
    // Edit
    Route::middleware('permission:purchases.edit')->group(function () {
        Route::get('/purchases/{purchaseOrder}/edit', PurchaseOrderForm::class)->name('purchases.edit');
    });
});
