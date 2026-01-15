<?php

namespace App\Modules\Agenda\Providers;

use App\Modules\Agenda\Livewire\Calendar;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AgendaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes.php');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'agenda');
        $this->registerLivewireComponents();
    }

    public function register(): void
    {
        //
    }

    private function registerLivewireComponents(): void
    {
        Livewire::component('agenda.calendar', Calendar::class);
    }
}
