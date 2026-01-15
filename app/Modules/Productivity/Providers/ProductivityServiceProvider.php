<?php

namespace App\Modules\Productivity\Providers;

use App\Modules\Productivity\Livewire\ProductivityDashboard;
use App\Modules\Productivity\Livewire\Projects\ProjectsList;
use App\Modules\Productivity\Livewire\Projects\ProjectForm;
use App\Modules\Productivity\Livewire\Projects\ProjectShow;
use App\Modules\Productivity\Livewire\Tasks\TasksKanban;
use App\Modules\Productivity\Livewire\Tasks\TaskShow;
use App\Modules\Productivity\Livewire\Tasks\TimeTracker;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class ProductivityServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes.php');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'productivity');
        $this->registerLivewireComponents();
    }

    public function register(): void
    {
        //
    }

    private function registerLivewireComponents(): void
    {
        Livewire::component('productivity.dashboard', ProductivityDashboard::class);
        Livewire::component('productivity.projects.list', ProjectsList::class);
        Livewire::component('productivity.projects.form', ProjectForm::class);
        Livewire::component('productivity.projects.show', ProjectShow::class);
        Livewire::component('productivity.tasks.kanban', TasksKanban::class);
        Livewire::component('productivity.tasks.show', TaskShow::class);
        Livewire::component('productivity.tasks.time-tracker', TimeTracker::class);
    }
}
