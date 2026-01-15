<?php

use Illuminate\Support\Facades\Route;
use App\Modules\CRM\Livewire\ContactsList;
use App\Modules\CRM\Livewire\ContactForm;
use App\Modules\CRM\Livewire\ContactShow;
use App\Modules\CRM\Livewire\OpportunityPipeline;
use App\Modules\CRM\Livewire\OpportunityForm;
use App\Modules\CRM\Livewire\OpportunityShow;
use App\Modules\CRM\Livewire\ProposalList;
use App\Modules\CRM\Livewire\ProposalForm;
use App\Modules\CRM\Livewire\ProposalShow;
use App\Modules\CRM\Livewire\OrderList;
use App\Modules\CRM\Livewire\OrderForm;
use App\Modules\CRM\Livewire\OrderShow;
use App\Modules\CRM\Livewire\ContractList;
use App\Modules\CRM\Livewire\ContractForm;
use App\Modules\CRM\Livewire\ContractShow;
use App\Modules\CRM\Livewire\ReminderList;
use App\Modules\CRM\Http\Controllers\OrderPdfController;
use App\Modules\CRM\Http\Controllers\ProposalPdfController;

Route::middleware(['web', 'auth'])->prefix('crm')->name('crm.')->group(function () {
    // === CONTACTS ===
    Route::middleware('permission:contacts.view')->group(function () {
        Route::get('/contacts', ContactsList::class)->name('contacts.index');
    });
    Route::middleware('permission:contacts.create')->group(function () {
        Route::get('/contacts/create', ContactForm::class)->name('contacts.create');
    });
    Route::middleware('permission:contacts.view')->group(function () {
        Route::get('/contacts/{contact}', ContactShow::class)->name('contacts.show');
    });
    Route::middleware('permission:contacts.edit')->group(function () {
        Route::get('/contacts/{contact}/edit', ContactForm::class)->name('contacts.edit');
    });

    // === OPPORTUNITIES ===
    Route::middleware('permission:opportunities.view')->group(function () {
        Route::get('/opportunities', OpportunityPipeline::class)->name('opportunities.index');
    });
    Route::middleware('permission:opportunities.create')->group(function () {
        Route::get('/opportunities/create', OpportunityForm::class)->name('opportunities.create');
    });
    Route::middleware('permission:opportunities.view')->group(function () {
        Route::get('/opportunities/{opportunity}', OpportunityShow::class)->name('opportunities.show');
    });
    Route::middleware('permission:opportunities.edit')->group(function () {
        Route::get('/opportunities/{opportunity}/edit', OpportunityForm::class)->name('opportunities.edit');
    });

    // === PROPOSALS ===
    Route::middleware('permission:opportunities.view')->group(function () {
        Route::get('/proposals', ProposalList::class)->name('proposals.index');
    });
    Route::middleware('permission:opportunities.create')->group(function () {
        Route::get('/proposals/create', ProposalForm::class)->name('proposals.create');
    });
    Route::middleware('permission:opportunities.view')->group(function () {
        Route::get('/proposals/{proposal}', ProposalShow::class)->name('proposals.show');
        Route::get('/proposals/{proposal}/pdf', [ProposalPdfController::class, 'download'])->name('proposals.pdf');
        Route::get('/proposals/{proposal}/pdf/stream', [ProposalPdfController::class, 'stream'])->name('proposals.pdf.stream');
    });
    Route::middleware('permission:opportunities.edit')->group(function () {
        Route::get('/proposals/{proposal}/edit', ProposalForm::class)->name('proposals.edit');
    });

    // === ORDERS ===
    Route::middleware('permission:orders.view')->group(function () {
        Route::get('/orders', OrderList::class)->name('orders.index');
    });
    Route::middleware('permission:orders.create')->group(function () {
        Route::get('/orders/create', OrderForm::class)->name('orders.create');
    });
    Route::middleware('permission:orders.view')->group(function () {
        Route::get('/orders/{order}', OrderShow::class)->name('orders.show');
        Route::get('/orders/{order}/pdf', [OrderPdfController::class, 'download'])->name('orders.pdf');
    });
    Route::middleware('permission:orders.edit')->group(function () {
        Route::get('/orders/{order}/edit', OrderForm::class)->name('orders.edit');
    });

    // === CONTRACTS ===
    Route::middleware('permission:contracts.view')->group(function () {
        Route::get('/contracts', ContractList::class)->name('contracts.index');
        Route::get('/reminders', ReminderList::class)->name('reminders.index');
    });
    Route::middleware('permission:contracts.create')->group(function () {
        Route::get('/contracts/create', ContractForm::class)->name('contracts.create');
    });
    Route::middleware('permission:contracts.view')->group(function () {
        Route::get('/contracts/{contract}', ContractShow::class)->name('contracts.show');
    });
    Route::middleware('permission:contracts.edit')->group(function () {
        Route::get('/contracts/{contract}/edit', ContractForm::class)->name('contracts.edit');
    });
});
