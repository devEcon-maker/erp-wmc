<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Agenda\Livewire\Calendar;

Route::middleware(['web', 'auth'])->prefix('agenda')->name('agenda.')->group(function () {
    // Calendrier - accessible avec permission events.view
    Route::middleware('permission:events.view')->group(function () {
        Route::get('/calendar', Calendar::class)->name('calendar');
    });
});
