<?php

namespace App\Modules\HR\Livewire\Tasks;

use App\Modules\HR\Models\Task;
use App\Modules\HR\Models\TaskStatus;
use Livewire\Component;

class TaskShow extends Component
{
    public Task $task;
    public $actual_hours = '';
    public $notes = '';

    public $showDeleteModal = false;
    public $showStatusModal = false;

    public function mount(Task $task)
    {
        $this->task = $task->load(['status', 'employee', 'assignedBy']);
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

    public function confirmDelete()
    {
        $this->showDeleteModal = true;
    }

    public function deleteTask()
    {
        $this->task->delete();

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Tache supprimee.'
        ]);

        return $this->redirect(route('hr.tasks.index'), navigate: true);
    }

    public function render()
    {
        $statuses = TaskStatus::orderBy('order')->get();

        return view('hr::livewire.tasks.task-show', [
            'statuses' => $statuses,
        ])->layout('layouts.app');
    }
}
