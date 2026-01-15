<?php

namespace App\Modules\Productivity\Livewire\Tasks;

use App\Modules\Productivity\Models\Task;
use App\Modules\Productivity\Models\TimeEntry;
use App\Modules\Productivity\Services\TaskService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class TaskShow extends Component
{
    public Task $task;

    public $showTimeModal = false;
    public $timeForm = [
        'hours' => '',
        'date' => '',
        'description' => '',
        'billable' => true,
    ];

    public $showSubtaskModal = false;
    public $subtaskForm = [
        'title' => '',
        'assigned_to' => '',
        'priority' => 'medium',
        'due_date' => '',
    ];

    protected $listeners = ['refreshTask' => '$refresh'];

    public function mount(Task $task)
    {
        $this->task = $task->load([
            'project.members',
            'assignee',
            'parent',
            'children.assignee',
            'timeEntries.employee',
        ]);
        $this->timeForm['date'] = now()->format('Y-m-d');
    }

    public function updateStatus(string $status, TaskService $service)
    {
        if (in_array($status, ['todo', 'in_progress', 'review', 'done'])) {
            $service->updateStatus($this->task, $status);
            $this->task->refresh();
            $this->dispatch('notify', message: 'Statut mis à jour.', type: 'success');
        }
    }

    public function updatePriority(string $priority)
    {
        if (in_array($priority, ['low', 'medium', 'high', 'urgent'])) {
            $this->task->update(['priority' => $priority]);
            $this->task->refresh();
            $this->dispatch('notify', message: 'Priorité mise à jour.', type: 'success');
        }
    }

    public function assignTo(?int $employeeId)
    {
        $this->task->update(['assigned_to' => $employeeId ?: null]);
        $this->task->refresh();
        $this->dispatch('notify', message: 'Assignation mise à jour.', type: 'success');
    }

    // Time Tracking
    public function openTimeModal()
    {
        $this->timeForm = [
            'hours' => '',
            'date' => now()->format('Y-m-d'),
            'description' => '',
            'billable' => true,
        ];
        $this->showTimeModal = true;
    }

    public function closeTimeModal()
    {
        $this->showTimeModal = false;
    }

    public function addTimeEntry()
    {
        $this->validate([
            'timeForm.hours' => 'required|numeric|min:0.1|max:24',
            'timeForm.date' => 'required|date',
            'timeForm.description' => 'nullable|string',
            'timeForm.billable' => 'boolean',
        ]);

        // Récupérer l'employee_id de l'utilisateur connecté
        $user = Auth::user();
        $employeeId = $user->employee?->id ?? $this->task->assigned_to;

        if (!$employeeId) {
            $this->addError('timeForm.hours', 'Impossible de déterminer l\'employé.');
            return;
        }

        TimeEntry::create([
            'employee_id' => $employeeId,
            'project_id' => $this->task->project_id,
            'task_id' => $this->task->id,
            'date' => $this->timeForm['date'],
            'hours' => $this->timeForm['hours'],
            'description' => $this->timeForm['description'] ?: null,
            'billable' => $this->timeForm['billable'],
        ]);

        $this->task->refresh();
        $this->closeTimeModal();
        $this->dispatch('notify', message: 'Temps ajouté.', type: 'success');
    }

    // Subtasks
    public function openSubtaskModal()
    {
        $this->subtaskForm = [
            'title' => '',
            'assigned_to' => '',
            'priority' => 'medium',
            'due_date' => '',
        ];
        $this->showSubtaskModal = true;
    }

    public function closeSubtaskModal()
    {
        $this->showSubtaskModal = false;
    }

    public function createSubtask(TaskService $service)
    {
        $this->validate([
            'subtaskForm.title' => 'required|string|max:255',
            'subtaskForm.assigned_to' => 'nullable|exists:employees,id',
            'subtaskForm.priority' => 'required|in:low,medium,high,urgent',
            'subtaskForm.due_date' => 'nullable|date',
        ]);

        $service->createSubtask($this->task, [
            'title' => $this->subtaskForm['title'],
            'assigned_to' => $this->subtaskForm['assigned_to'] ?: null,
            'priority' => $this->subtaskForm['priority'],
            'due_date' => $this->subtaskForm['due_date'] ?: null,
            'status' => 'todo',
        ]);

        $this->task->refresh();
        $this->closeSubtaskModal();
        $this->dispatch('notify', message: 'Sous-tâche créée.', type: 'success');
    }

    public function delete()
    {
        $projectId = $this->task->project_id;
        $this->task->delete();

        return redirect()->route('productivity.projects.tasks', $projectId)
            ->with('success', 'Tâche supprimée.');
    }

    public function render()
    {
        return view('livewire.productivity.tasks.task-show')->layout('layouts.app');
    }
}
