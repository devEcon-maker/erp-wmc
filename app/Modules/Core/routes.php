<?php

use App\Modules\Core\Livewire\Dashboard;
use App\Modules\Core\Livewire\GlobalSearch;
use App\Modules\Core\Livewire\NotificationsList;
use App\Modules\Core\Livewire\Admin\UserManagement;
use App\Modules\Core\Livewire\Admin\RoleManagement;
use App\Modules\Core\Livewire\Admin\Settings;
use App\Modules\Core\Livewire\BugReportList;
use App\Modules\Core\Livewire\ProfileEdit;
use App\Modules\Core\Livewire\Help;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/notifications', NotificationsList::class)->name('notifications.index');
    Route::get('/profile', ProfileEdit::class)->name('profile');
    Route::get('/help', Help::class)->name('help');

    // Bug Reports - Accessible a tous les utilisateurs connectes
    Route::get('/bug-reports', BugReportList::class)->name('bug-reports.index');

    // Administration - Réservé aux super_admin et admin
    Route::middleware(['can:settings.manage'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', UserManagement::class)->name('users.index');
        Route::get('/roles', RoleManagement::class)->name('roles.index');
        Route::get('/settings', Settings::class)->name('settings.index');
    });
});
