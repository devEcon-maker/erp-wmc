<?php

namespace App\Modules\HR\Livewire\Tasks;

use App\Modules\HR\Models\Task;
use App\Modules\HR\Models\TaskStatus;
use App\Modules\HR\Models\Employee;
use App\Notifications\EmployeeTaskAssigned;
use Livewire\Component;

class TaskShow extends Component
{
    public Task $task;
    public $actual_hours = '';
    public $notes = '';

    public $showDeleteModal = false;
    public $showStatusModal = false;
    public $showAssigneeModal = false;
    public $selectedEmployeeId = '';

    public function mount(Task $task)
    {
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

    // Gestion des assignes
    public function openAssigneeModal()
    {
        $this->selectedEmployeeId = '';
        $this->showAssigneeModal = true;
    }

    public function closeAssigneeModal()
    {
        $this->showAssigneeModal = false;
        $this->selectedEmployeeId = '';
    }

    public function addAssignee()
    {
        $this->validate([
            'selectedEmployeeId' => 'required|exists:employees,id',
        ]);

        // Verifier que l'employe n'est pas deja assigne
        if ($this->task->assignees()->where('employees.id', $this->selectedEmployeeId)->exists()) {
            $this->addError('selectedEmployeeId', 'Cet employe est deja assigne a cette tache.');
            return;
        }

        // Verifier que ce n'est pas le proprietaire de la tache
        if ($this->task->employee_id == $this->selectedEmployeeId) {
            $this->addError('selectedEmployeeId', 'Cet employe est deja le proprietaire de la tache.');
            return;
        }

        $employee = Employee::find($this->selectedEmployeeId);

        $this->task->assignees()->attach($this->selectedEmployeeId, [
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
        ]);

        // Envoyer une notification a l'employe s'il a un compte utilisateur
        if ($employee && $employee->user) {
            $employee->user->notify(new EmployeeTaskAssigned(
                $this->task,
                auth()->user()->name
            ));
        }

        $this->task->load('assignees');
        $this->closeAssigneeModal();

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Employe ajoute a la tache.'
        ]);
    }

    public function removeAssignee($employeeId)
    {
        $this->task->assignees()->detach($employeeId);
        $this->task->load('assignees');

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Employe retire de la tache.'
        ]);
    }

    public function render()
    {
        $statuses = TaskStatus::orderBy('order')->get();

        // Employes disponibles pour assignation (exclure le proprietaire et les deja assignes)
        $assignedIds = $this->task->assignees->pluck('id')->toArray();
        $assignedIds[] = $this->task->employee_id;

        $availableEmployees = Employee::where('status', 'active')
            ->whereNotIn('id', $assignedIds)
            ->orderBy('last_name')
            ->get();

        return view('hr::livewire.tasks.task-show', [
            'statuses' => $statuses,
            'availableEmployees' => $availableEmployees,
        ])->layout('layouts.app');
    }
}
