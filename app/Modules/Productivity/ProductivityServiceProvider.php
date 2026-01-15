<?php

namespace App\Modules\Productivity;

use App\Modules\Productivity\Livewire\ProductivityDashboard;
use App\Modules\Productivity\Livewire\Projects\ProjectsList;
use App\Modules\Productivity\Livewire\Projects\ProjectForm;
use App\Modules\Productivity\Livewire\Projects\ProjectShow;
use App\Modules\Productivity\Livewire\Tasks\TasksKanban;
use App\Modules\Productivity\Livewire\Tasks\TaskShow;
use App\Modules\Productivity\Livewire\Tasks\TimeTracker;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

class ProductivityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            Services\ProjectService::class,
            Services\ProjectService::class
        );

        $this->app->singleton(
            Services\TaskService::class,
            Services\TaskService::class
        );

        $this->app->singleton(
            Services\ProjectStatsService::class,
            Services\ProjectStatsService::class
        );
    }

    public function boot(): void
    {
        $this->loadMigrations();
        $this->registerRoutes();
        $this->registerLivewireComponents();
    }

    private function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }

    private function registerRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('productivity')
            ->name('productivity.')
            ->group(function () {
                // Dashboard
                Route::get('/', ProductivityDashboard::class)->name('dashboard');

                // Projects
                Route::get('/projects', ProjectsList::class)->name('projects.index');
                Route::get('/projects/create', ProjectForm::class)->name('projects.create');
                Route::get('/projects/{project}', ProjectShow::class)->name('projects.show');
                Route::get('/projects/{project}/edit', ProjectForm::class)->name('projects.edit');
                Route::get('/projects/{project}/tasks', TasksKanban::class)->name('projects.tasks');

                // Tasks
                Route::get('/tasks/{task}', TaskShow::class)->name('tasks.show');

                // Time Tracker
                Route::get('/time-tracker', TimeTracker::class)->name('time-tracker');
            });
    }

    private function registerLivewireComponents(): void
    {
        // Dashboard
        Livewire::component('productivity.dashboard', ProductivityDashboard::class);

        // Projects
        Livewire::component('productivity.projects.list', ProjectsList::class);
        Livewire::component('productivity.projects.form', ProjectForm::class);
        Livewire::component('productivity.projects.show', ProjectShow::class);

        // Tasks
        Livewire::component('productivity.tasks.kanban', TasksKanban::class);
        Livewire::component('productivity.tasks.show', TaskShow::class);
        Livewire::component('productivity.tasks.time-tracker', TimeTracker::class);
    }
}
