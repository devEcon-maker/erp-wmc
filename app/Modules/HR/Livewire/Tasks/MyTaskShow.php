<?php

namespace App\Modules\HR\Livewire\Tasks;

use App\Modules\HR\Models\Task;
use App\Modules\HR\Models\TaskStatus;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class MyTaskShow extends Component
{
    public Task $task;
    public $actual_hours = '';
    public $notes = '';

    public $showStatusModal = false;

    public function mount(Task $task)
    {
        // Verifier que l'utilisateur est assigne a cette tache
        $currentEmployee = Auth::user()?->employee;

        if (!$currentEmployee) {
            abort(403, 'Vous devez etre associe a un employe.');
        }

        $isAssigned = $task->employee_id == $currentEmployee->id
            || $task->assignees()->where('employees.id', $currentEmployee->id)->exists();

        if (!$isAssigned) {
            abort(403, 'Vous n\'etes pas assigne a cette tache.');
        }

        $this->task = $task->load(['status', 'employee', 'assignedBy', 'assignees']);
        $this->actual_hours = $task->actual_hours ?? '';
        $this->notes = $task->notes ?? '';
    }

    public function updateStatus($statusId)
    {
        $this->task->updateStatus($statusId);
        $this->task->refresh();

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Statut mis a jour.'
        ]);

        $this->showStatusModal = false;
    }

    public function updateProgress()
    {
        $this->validate([
            'actual_hours' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $this->task->update([
            'actual_hours' => $this->actual_hours ?: null,
            'notes' => $this->notes ?: null,
        ]);

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Progression mise a jour.'
        ]);
    }

    public function markAsCompleted()
    {
        $this->task->markAsCompleted();
        $this->task->refresh();

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Tache terminee.'
        ]);
    }

    public function render()
    {
        $statuses = TaskStatus::orderBy('order')->get();

        return view('hr::livewire.tasks.my-task-show', [
            'statuses' => $statuses,
        ])->layout('layouts.app');
    }
}
