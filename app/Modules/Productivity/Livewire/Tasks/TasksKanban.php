<?php

namespace App\Modules\Productivity\Livewire\Tasks;

use App\Modules\Productivity\Models\Project;
use App\Modules\Productivity\Models\Task;
use App\Modules\Productivity\Services\TaskService;
use Livewire\Component;

class TasksKanban extends Component
{
    public ?Project $project = null;

    public $statuses = [
        'todo' => ['label' => 'À faire', 'color' => 'blue', 'icon' => 'radio_button_unchecked'],
        'in_progress' => ['label' => 'En cours', 'color' => 'yellow', 'icon' => 'pending'],
        'review' => ['label' => 'Revue', 'color' => 'purple', 'icon' => 'rate_review'],
        'done' => ['label' => 'Terminé', 'color' => 'green', 'icon' => 'check_circle'],
    ];

    // Modal de création/édition
    public $showTaskModal = false;
    public $editingTask = null;
    public $taskForm = [
        'title' => '',
        'description' => '',
        'assigned_to' => '',
        'priority' => 'medium',
        'due_date' => '',
        'estimated_hours' => '',
        'status' => 'todo',
        'project_id' => '',
    ];

    protected $listeners = ['refreshKanban' => '$refresh'];

    public function mount(?Project $project = null)
    {
        if ($project && $project->exists) {
            $this->project = $project->load(['members', 'tasks.assignee']);
        }
    }

    public function getTasksByStatusProperty(): array
    {
        if ($this->project) {
            $tasks = $this->project->tasks()
                ->with(['assignee', 'project'])
                ->rootTasks()
                ->ordered()
                ->get();
        } else {
            $tasks = Task::with(['assignee', 'project'])
                ->rootTasks()
                ->ordered()
                ->get();
        }

        $byStatus = [];
        foreach ($this->statuses as $status => $config) {
            $byStatus[$status] = $tasks->where('status', $status)->values();
        }

        return $byStatus;
    }

    public function updateTaskStatus($taskId, $newStatus, TaskService $service)
    {
        $task = Task::find($taskId);

        $canUpdate = $task && array_key_exists($newStatus, $this->statuses);
        if ($this->project) {
            $canUpdate = $canUpdate && $task->project_id === $this->project->id;
        }

        if ($canUpdate) {
            $service->updateStatus($task, $newStatus);
            $this->project?->refresh();
            $this->dispatch('notify', message: 'Tâche déplacée.', type: 'success');
        }
    }

    public function openTaskModal($status = 'todo')
    {
        $this->resetTaskForm();
        $this->taskForm['status'] = $status;
        $this->showTaskModal = true;
    }

    public function editTask($taskId)
    {
        $task = Task::find($taskId);
        $canEdit = $task && (!$this->project || $task->project_id === $this->project->id);

        if ($canEdit) {
            $this->editingTask = $task;
            $this->taskForm = [
                'title' => $task->title,
                'description' => $task->description ?? '',
                'assigned_to' => $task->assigned_to ?? '',
                'priority' => $task->priority,
                'due_date' => $task->due_date?->format('Y-m-d') ?? '',
                'estimated_hours' => $task->estimated_hours ?? '',
                'status' => $task->status,
                'project_id' => $task->project_id ?? '',
            ];
            $this->showTaskModal = true;
        }
    }

    public function closeTaskModal()
    {
        $this->showTaskModal = false;
        $this->editingTask = null;
        $this->resetTaskForm();
    }

    private function resetTaskForm()
    {
        $this->taskForm = [
            'title' => '',
            'description' => '',
            'assigned_to' => $this->project?->manager_id ?? '',
            'priority' => 'medium',
            'due_date' => '',
            'estimated_hours' => '',
            'status' => 'todo',
            'project_id' => $this->project?->id ?? '',
        ];
    }

    public function saveTask(TaskService $service)
    {
        $this->validate([
            'taskForm.title' => 'required|string|max:255',
            'taskForm.description' => 'nullable|string',
            'taskForm.assigned_to' => 'nullable|exists:employees,id',
            'taskForm.priority' => 'required|in:low,medium,high,urgent',
            'taskForm.due_date' => 'nullable|date',
            'taskForm.estimated_hours' => 'nullable|numeric|min:0',
            'taskForm.status' => 'required|in:todo,in_progress,review,done',
        ]);

        $data = [
            'title' => $this->taskForm['title'],
            'description' => $this->taskForm['description'] ?: null,
            'assigned_to' => $this->taskForm['assigned_to'] ?: null,
            'priority' => $this->taskForm['priority'],
            'due_date' => $this->taskForm['due_date'] ?: null,
            'estimated_hours' => $this->taskForm['estimated_hours'] ?: null,
            'status' => $this->taskForm['status'],
        ];

        if ($this->editingTask) {
            $service->update($this->editingTask, $data);
            $message = 'Tâche mise à jour.';
        } else {
            $data['project_id'] = $this->project?->id ?? ($this->taskForm['project_id'] ?: null);
            $service->create($data);
            $message = 'Tâche créée.';
        }

        $this->project?->refresh();
        $this->closeTaskModal();
        $this->dispatch('notify', message: $message, type: 'success');
    }

    public function deleteTask($taskId, TaskService $service)
    {
        $task = Task::find($taskId);
        $canDelete = $task && (!$this->project || $task->project_id === $this->project->id);

        if ($canDelete) {
            $task->delete();
            $this->project?->refresh();
            $this->dispatch('notify', message: 'Tâche supprimée.', type: 'success');
        }
    }

    public function duplicateTask($taskId, TaskService $service)
    {
        $task = Task::find($taskId);
        $canDuplicate = $task && (!$this->project || $task->project_id === $this->project->id);

        if ($canDuplicate) {
            $service->duplicate($task);
            $this->project?->refresh();
            $this->dispatch('notify', message: 'Tâche dupliquée.', type: 'success');
        }
    }

    public function render()
    {
        return view('livewire.productivity.tasks.tasks-kanban', [
            'tasksByStatus' => $this->tasksByStatus,
        ])->layout('layouts.app');
    }
}
