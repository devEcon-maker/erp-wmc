<?php

namespace App\Modules\HR\Livewire\Tasks;

use App\Modules\HR\Models\Task;
use App\Modules\HR\Models\TaskStatus;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class MyTasks extends Component
{
    use WithPagination;

    public $statusId = '';
    public $priority = '';
    public $view = 'list'; // 'list' ou 'board'

    public $showQuickAddModal = false;
    public $quickTitle = '';
    public $quickPriority = 'medium';
    public $quickDueDate = '';

    protected $queryString = [
        'statusId' => ['except' => ''],
        'priority' => ['except' => ''],
        'view' => ['except' => 'list'],
    ];

    public function getEmployeeProperty()
    {
        return Auth::user()?->employee;
    }

    public function getStatsProperty()
    {
        if (!$this->employee) {
            return ['total' => 0, 'pending' => 0, 'completed_today' => 0, 'overdue' => 0];
        }

        $baseQuery = Task::forEmployee($this->employee->id);

        return [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->pending()->count(),
            'completed_today' => (clone $baseQuery)->completed()->whereDate('completed_at', today())->count(),
            'overdue' => (clone $baseQuery)->overdue()->count(),
        ];
    }

    public function toggleView()
    {
        $this->view = $this->view === 'list' ? 'board' : 'list';
    }

    public function quickStatusUpdate($taskId, $statusId)
    {
        $task = Task::find($taskId);
        if ($task && $task->employee_id === $this->employee?->id) {
            $task->updateStatus($statusId);
            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Statut mis a jour.'
            ]);
        }
    }

    public function openQuickAdd()
    {
        $this->reset(['quickTitle', 'quickPriority', 'quickDueDate']);
        $this->quickPriority = 'medium';
        $this->showQuickAddModal = true;
    }

    public function quickAdd()
    {
        $this->validate([
            'quickTitle' => 'required|string|max:255',
            'quickPriority' => 'required|in:low,medium,high,urgent',
            'quickDueDate' => 'nullable|date',
        ]);

        if (!$this->employee) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Vous devez etre associe a un employe.'
            ]);
            return;
        }

        $defaultStatus = TaskStatus::getDefault();

        Task::create([
            'title' => $this->quickTitle,
            'status_id' => $defaultStatus->id,
            'employee_id' => $this->employee->id,
            'assigned_by' => Auth::id(),
            'priority' => $this->quickPriority,
            'due_date' => $this->quickDueDate ?: null,
        ]);

        $this->showQuickAddModal = false;
        $this->reset(['quickTitle', 'quickPriority', 'quickDueDate']);

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Tache ajoutee.'
        ]);
    }

    public function getTasksForStatus($statusId)
    {
        if (!$this->employee) {
            return collect();
        }

        return Task::forEmployee($this->employee->id)
            ->where('status_id', $statusId)
            ->when($this->priority, fn($q) => $q->where('priority', $this->priority))
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
            ->orderBy('due_date')
            ->get();
    }

    public function render()
    {
        $statuses = TaskStatus::orderBy('order')->get();

        $tasks = collect();
        if ($this->employee && $this->view === 'list') {
            $tasks = Task::forEmployee($this->employee->id)
                ->when($this->statusId, fn($q) => $q->where('status_id', $this->statusId))
                ->when($this->priority, fn($q) => $q->where('priority', $this->priority))
                ->with(['status'])
                ->orderByRaw("CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC")
                ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
                ->paginate(15);
        }

        return view('hr::livewire.tasks.my-tasks', [
            'tasks' => $tasks,
            'statuses' => $statuses,
            'stats' => $this->stats,
            'priorities' => Task::PRIORITIES,
        ])->layout('layouts.app');
    }
}
