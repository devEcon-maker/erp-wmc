<?php

namespace App\Modules\HR\Livewire\Tasks;

use App\Modules\HR\Models\Task;
use App\Modules\HR\Models\TaskStatus;
use App\Modules\HR\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;

class TasksList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusId = '';
    public $priority = '';
    public $employeeId = '';
    public $dateFilter = '';

    public $showDeleteModal = false;
    public $taskToDelete = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusId' => ['except' => ''],
        'priority' => ['except' => ''],
        'employeeId' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($taskId)
    {
        $this->taskToDelete = Task::find($taskId);
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->taskToDelete = null;
    }

    public function deleteTask()
    {
        if ($this->taskToDelete) {
            $title = $this->taskToDelete->title;
            $this->taskToDelete->delete();

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Tache \"{$title}\" supprimee avec succes."
            ]);
        }

        $this->showDeleteModal = false;
        $this->taskToDelete = null;
    }

    public function quickStatusUpdate($taskId, $statusId)
    {
        $task = Task::find($taskId);
        if ($task) {
            $task->updateStatus($statusId);
            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Statut mis a jour.'
            ]);
        }
    }

    public function getStatsProperty()
    {
        return [
            'total' => Task::count(),
            'pending' => Task::pending()->count(),
            'completed' => Task::completed()->count(),
            'overdue' => Task::overdue()->count(),
        ];
    }

    public function render()
    {
        $tasks = Task::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusId, function ($query) {
                $query->where('status_id', $this->statusId);
            })
            ->when($this->priority, function ($query) {
                $query->where('priority', $this->priority);
            })
            ->when($this->employeeId, function ($query) {
                $query->forEmployee($this->employeeId);
            })
            ->when($this->dateFilter, function ($query) {
                match ($this->dateFilter) {
                    'today' => $query->whereDate('due_date', today()),
                    'week' => $query->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()]),
                    'overdue' => $query->overdue(),
                    default => $query,
                };
            })
            ->with(['status', 'employee', 'assignedBy', 'assignees'])
            ->orderByRaw("CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC")
            ->orderBy('priority', 'desc')
            ->paginate(20);

        $statuses = TaskStatus::orderBy('order')->get();
        $employees = Employee::active()->orderBy('last_name')->get();

        return view('hr::livewire.tasks.tasks-list', [
            'tasks' => $tasks,
            'statuses' => $statuses,
            'employees' => $employees,
            'stats' => $this->stats,
        ])->layout('layouts.app');
    }
}
