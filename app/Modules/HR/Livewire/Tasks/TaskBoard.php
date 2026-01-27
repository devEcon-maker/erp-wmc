<?php

namespace App\Modules\HR\Livewire\Tasks;

use App\Modules\HR\Models\Task;
use App\Modules\HR\Models\TaskStatus;
use App\Modules\HR\Models\Employee;
use Livewire\Component;

class TaskBoard extends Component
{
    public $statuses;
    public $employeeId = '';
    public $priority = '';

    public $showDeleteModal = false;
    public $taskToDelete = null;

    protected $queryString = [
        'employeeId' => ['except' => ''],
        'priority' => ['except' => ''],
    ];

    public function mount()
    {
        $this->statuses = TaskStatus::orderBy('order')->get();
    }

    public function updateStatus($taskId, $statusId)
    {
        $task = Task::find($taskId);
        $status = TaskStatus::find($statusId);

        if ($task && $status) {
            $oldStatus = $task->status->name;
            $task->updateStatus($statusId);

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Tache deplacee de {$oldStatus} vers {$status->name}"
            ]);

            return $this->redirect(route('hr.tasks.board'), navigate: true);
        }
    }

    public function confirmDelete($taskId)
    {
        $this->taskToDelete = Task::find($taskId);
        $this->showDeleteModal = true;
    }

    public function deleteTask()
    {
        if ($this->taskToDelete) {
            $title = $this->taskToDelete->title;
            $this->taskToDelete->delete();

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Tache \"{$title}\" supprimee avec succes"
            ]);

            $this->showDeleteModal = false;
            $this->taskToDelete = null;

            return $this->redirect(route('hr.tasks.board'), navigate: true);
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->taskToDelete = null;
    }

    public function getTasksForStatus($statusId)
    {
        return Task::with(['employee', 'assignedBy', 'assignees'])
            ->where('status_id', $statusId)
            ->when($this->employeeId, fn($q) => $q->forEmployee($this->employeeId))
            ->when($this->priority, fn($q) => $q->where('priority', $this->priority))
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
            ->orderBy('due_date')
            ->get();
    }

    public function getStatsProperty()
    {
        $query = Task::query()
            ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
            ->when($this->priority, fn($q) => $q->where('priority', $this->priority));

        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->pending()->count(),
            'overdue' => (clone $query)->overdue()->count(),
        ];
    }

    public function render()
    {
        $employees = Employee::active()->orderBy('last_name')->get();

        return view('hr::livewire.tasks.task-board', [
            'employees' => $employees,
            'stats' => $this->stats,
            'priorities' => Task::PRIORITIES,
        ])->layout('layouts.app');
    }
}
