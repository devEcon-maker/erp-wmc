<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Productivity\Livewire\ProductivityDashboard;
use App\Modules\Productivity\Livewire\Projects\ProjectsList;
use App\Modules\Productivity\Livewire\Projects\ProjectForm;
use App\Modules\Productivity\Livewire\Projects\ProjectShow;
use App\Modules\Productivity\Livewire\Tasks\TasksKanban;
use App\Modules\Productivity\Livewire\Tasks\TaskShow;
use App\Modules\Productivity\Livewire\Tasks\TimeTracker;

Route::middleware(['web', 'auth'])->prefix('productivity')->name('productivity.')->group(function () {
    // Dashboard - accessible avec permission projects.view ou tasks.view
    Route::middleware('permission:projects.view|tasks.view')->group(function () {
        Route::get('/', ProductivityDashboard::class)->name('dashboard');
    });

    // Projets - Liste
    Route::middleware('permission:projects.view')->group(function () {
        Route::get('/projects', ProjectsList::class)->name('projects.index');
    });

    // Projets - Create - AVANT /{project}
    Route::middleware('permission:projects.create')->group(function () {
        Route::get('/projects/create', ProjectForm::class)->name('projects.create');
    });

    // Projets - View et Edit - APRES /create
    Route::middleware('permission:projects.view')->group(function () {
        Route::get('/projects/{project}', ProjectShow::class)->name('projects.show');
        Route::get('/projects/{project}/tasks', TasksKanban::class)->name('projects.tasks');
    });

    Route::middleware('permission:projects.edit')->group(function () {
        Route::get('/projects/{project}/edit', ProjectForm::class)->name('projects.edit');
    });

    // Taches - View (Kanban board global)
    Route::middleware('permission:tasks.view')->group(function () {
        Route::get('/tasks', TasksKanban::class)->name('tasks.index');
        Route::get('/tasks/{task}', TaskShow::class)->name('tasks.show');
    });

    // Suivi du temps - accessible avec permission time.view
    Route::middleware('permission:time.view')->group(function () {
        Route::get('/time-tracker', TimeTracker::class)->name('time-tracker');
    });
});
