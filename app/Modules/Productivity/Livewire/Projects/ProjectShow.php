<?php

namespace App\Modules\Productivity\Livewire\Projects;

use App\Modules\Productivity\Models\Project;
use App\Modules\Productivity\Models\Task;
use App\Modules\Productivity\Models\TimeEntry;
use App\Modules\Productivity\Services\ProjectService;
use App\Modules\HR\Models\Employee;
use Livewire\Component;

class ProjectShow extends Component
{
    public Project $project;
    public string $activeTab = 'overview';

    // Pour l'ajout de membre
    public $showMemberModal = false;
    public $newMember = [
        'employee_id' => '',
        'role' => '',
        'hourly_rate' => '',
    ];

    // Pour l'ajout rapide de tâche
    public $showQuickTaskModal = false;
    public $quickTask = [
        'title' => '',
        'assigned_to' => '',
        'priority' => 'medium',
        'due_date' => '',
    ];

    protected $listeners = ['taskUpdated' => '$refresh', 'timeEntryAdded' => '$refresh'];

    public function mount(Project $project)
    {
        $this->project = $project->load([
            'contact',
            'manager',
            'members',
            'tasks.assignee',
            'timeEntries.employee',
        ]);
    }

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    // Gestion des membres
    public function openMemberModal()
    {
        $this->newMember = ['employee_id' => '', 'role' => '', 'hourly_rate' => ''];
        $this->showMemberModal = true;
    }

    public function closeMemberModal()
    {
        $this->showMemberModal = false;
    }

    public function addMember(ProjectService $service)
    {
        $this->validate([
            'newMember.employee_id' => 'required|exists:employees,id',
            'newMember.role' => 'nullable|string|max:100',
            'newMember.hourly_rate' => 'nullable|numeric|min:0',
        ]);

        // Vérifier si l'employé est déjà membre
        if ($this->project->members()->where('employee_id', $this->newMember['employee_id'])->exists()) {
            $this->addError('newMember.employee_id', 'Cet employé est déjà membre du projet.');
            return;
        }

        $service->addMember(
            $this->project,
            $this->newMember['employee_id'],
            $this->newMember['role'] ?: null,
            $this->newMember['hourly_rate'] ?: null
        );

        $this->project->refresh();
        $this->closeMemberModal();
        $this->dispatch('notify', message: 'Membre ajouté au projet.', type: 'success');
    }

    public function removeMember(int $employeeId, ProjectService $service)
    {
        $service->removeMember($this->project, $employeeId);
        $this->project->refresh();
        $this->dispatch('notify', message: 'Membre retiré du projet.', type: 'success');
    }

    // Gestion rapide des tâches
    public function openQuickTaskModal()
    {
        $this->quickTask = ['title' => '', 'assigned_to' => $this->project->manager_id, 'priority' => 'medium', 'due_date' => ''];
        $this->showQuickTaskModal = true;
    }

    public function closeQuickTaskModal()
    {
        $this->showQuickTaskModal = false;
    }

    public function createQuickTask()
    {
        $this->validate([
            'quickTask.title' => 'required|string|max:255',
            'quickTask.assigned_to' => 'nullable|exists:employees,id',
            'quickTask.priority' => 'required|in:low,medium,high,urgent',
            'quickTask.due_date' => 'nullable|date',
        ]);

        Task::create([
            'project_id' => $this->project->id,
            'title' => $this->quickTask['title'],
            'assigned_to' => $this->quickTask['assigned_to'] ?: null,
            'priority' => $this->quickTask['priority'],
            'due_date' => $this->quickTask['due_date'] ?: null,
            'status' => 'todo',
            'order' => Task::where('project_id', $this->project->id)->where('status', 'todo')->max('order') + 1,
        ]);

        $this->project->refresh();
        $this->closeQuickTaskModal();
        $this->dispatch('notify', message: 'Tâche créée.', type: 'success');
    }

    // Actions sur le projet
    public function activate(ProjectService $service)
    {
        $service->activate($this->project);
        $this->project->refresh();
        $this->dispatch('notify', message: 'Projet activé.', type: 'success');
    }

    public function putOnHold(ProjectService $service)
    {
        $service->putOnHold($this->project);
        $this->project->refresh();
        $this->dispatch('notify', message: 'Projet mis en pause.', type: 'success');
    }

    public function complete(ProjectService $service)
    {
        $service->complete($this->project);
        $this->project->refresh();
        $this->dispatch('notify', message: 'Projet terminé.', type: 'success');
    }

    public function cancel(ProjectService $service)
    {
        $service->cancel($this->project);
        $this->project->refresh();
        $this->dispatch('notify', message: 'Projet annulé.', type: 'success');
    }

    public function delete()
    {
        $this->project->delete();
        return redirect()->route('productivity.projects.index')
            ->with('success', 'Projet supprimé.');
    }

    // Propriétés calculées
    public function getTasksStatsProperty(): array
    {
        $tasks = $this->project->tasks;
        $total = $tasks->count();

        return [
            'total' => $total,
            'todo' => $tasks->where('status', 'todo')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'review' => $tasks->where('status', 'review')->count(),
            'done' => $tasks->where('status', 'done')->count(),
            'overdue' => $tasks->filter(fn($t) => $t->is_overdue)->count(),
            'progress' => $total > 0 ? round(($tasks->where('status', 'done')->count() / $total) * 100) : 0,
        ];
    }

    public function getTimeStatsProperty(): array
    {
        $timeEntries = $this->project->timeEntries;

        return [
            'total_hours' => $timeEntries->sum('hours'),
            'billable_hours' => $timeEntries->where('billable', true)->sum('hours'),
            'total_cost' => $timeEntries->sum(fn($e) => $e->cost),
            'approved_hours' => $timeEntries->where('approved', true)->sum('hours'),
            'pending_hours' => $timeEntries->whereNull('approved')->sum('hours'),
        ];
    }

    public function getFinanceStatsProperty(): array
    {
        return [
            'budget' => $this->project->budget,
            'supplier_cost' => $this->project->supplier_cost ?? 0,
            'total_cost' => $this->project->total_cost,
            'gap' => $this->project->gap,
            'invoiced_amount' => $this->project->invoiced_amount,
            'revenue' => $this->project->revenue,
            'profit' => $this->project->profit,
            'profit_margin' => round($this->project->profit_margin, 1),
            'budget_usage' => $this->project->budget > 0
                ? round(($this->project->total_cost / $this->project->budget) * 100)
                : 0,
        ];
    }

    public function getRecentTimeEntriesProperty()
    {
        return $this->project->timeEntries()
            ->with('employee', 'task')
            ->latest('date')
            ->limit(10)
            ->get();
    }

    public function getAvailableEmployeesProperty()
    {
        $memberIds = $this->project->members->pluck('id')->toArray();
        return Employee::where('status', 'active')
            ->whereNotIn('id', $memberIds)
            ->orderBy('last_name')
            ->get();
    }

    public function render()
    {
        return view('livewire.productivity.projects.project-show', [
            'tasksStats' => $this->tasksStats,
            'timeStats' => $this->timeStats,
            'financeStats' => $this->financeStats,
            'recentTimeEntries' => $this->recentTimeEntries,
            'availableEmployees' => $this->availableEmployees,
        ])->layout('layouts.app');
    }
}
