<?php

namespace App\Modules\Agenda;

use App\Modules\Agenda\Livewire\Calendar;
use App\Modules\Agenda\Console\Commands\SendEventReminders;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Console\Scheduling\Schedule;
use Livewire\Livewire;

class AgendaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            Services\EventService::class,
            Services\EventService::class
        );
    }

    public function boot(): void
    {
        $this->loadMigrations();
        $this->registerRoutes();
        $this->registerLivewireComponents();
        $this->registerCommands();
        $this->scheduleCommands();
    }

    private function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }

    private function registerRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('agenda')
            ->name('agenda.')
            ->group(function () {
                Route::get('/', Calendar::class)->name('calendar');
            });
    }

    private function registerLivewireComponents(): void
    {
        Livewire::component('agenda.calendar', Calendar::class);
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SendEventReminders::class,
            ]);
        }
    }

    private function scheduleCommands(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // Exécuter la commande de rappels toutes les minutes
            $schedule->command('events:send-reminders')
                ->everyMinute()
                ->withoutOverlapping();
        });
    }
}
