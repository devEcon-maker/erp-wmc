<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Finance\Livewire\InvoiceList;
use App\Modules\Finance\Livewire\InvoiceForm;
use App\Modules\Finance\Livewire\InvoiceShow;
use App\Modules\Finance\Livewire\SubscriptionList;
use App\Modules\Finance\Http\Controllers\InvoicePdfController;

Route::middleware(['web', 'auth'])->prefix('finance')->name('finance.')->group(function () {
    // === FACTURES ===
    Route::middleware('permission:invoices.view')->group(function () {
        Route::get('/invoices', InvoiceList::class)->name('invoices.index');
    });
    // Create - AVANT /{invoice}
    Route::middleware('permission:invoices.create')->group(function () {
        Route::get('/invoices/create', InvoiceForm::class)->name('invoices.create');
    });
    // View et PDF - APRES /create
    Route::middleware('permission:invoices.view')->group(function () {
        Route::get('/invoices/{invoice}', InvoiceShow::class)->name('invoices.show');
        Route::get('/invoices/{invoice}/pdf', [InvoicePdfController::class, 'download'])->name('invoices.pdf');
    });
    // Edit
    Route::middleware('permission:invoices.edit')->group(function () {
        Route::get('/invoices/{invoice}/edit', InvoiceForm::class)->name('invoices.edit');
    });

    // === ABONNEMENTS ===
    Route::middleware('permission:invoices.view')->group(function () {
        Route::get('/subscriptions', SubscriptionList::class)->name('subscriptions.index');
    });
});
