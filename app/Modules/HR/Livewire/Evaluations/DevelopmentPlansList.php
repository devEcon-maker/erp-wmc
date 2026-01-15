<?php

namespace App\Modules\HR\Livewire\Evaluations;

use App\Modules\HR\Models\DevelopmentPlan;
use App\Modules\HR\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;

class DevelopmentPlansList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $skillAreaFilter = '';
    public $employeeFilter = '';

    // Modal création
    public $showCreateModal = false;
    public $planForm = [
        'employee_id' => '',
        'title' => '',
        'description' => '',
        'skill_area' => 'technical',
        'action_type' => 'training',
        'start_date' => '',
        'target_date' => '',
        'resources_needed' => '',
        'budget' => '',
    ];

    // Modal progression
    public $showProgressModal = false;
    public $selectedPlan = null;
    public $progressPercentage = 0;
    public $completionNotes = '';

    protected $queryString = ['search', 'statusFilter', 'skillAreaFilter'];

    public function mount()
    {
        $this->planForm['start_date'] = date('Y-m-d');
        $this->planForm['target_date'] = date('Y-m-d', strtotime('+3 months'));
    }

    public function openCreateModal()
    {
        $this->reset('planForm');
        $this->planForm['start_date'] = date('Y-m-d');
        $this->planForm['target_date'] = date('Y-m-d', strtotime('+3 months'));
        $this->planForm['skill_area'] = 'technical';
        $this->planForm['action_type'] = 'training';
        $this->showCreateModal = true;
    }

    public function createPlan()
    {
        $this->validate([
            'planForm.employee_id' => 'required|exists:employees,id',
            'planForm.title' => 'required|string|max:255',
            'planForm.description' => 'nullable|string|max:1000',
            'planForm.skill_area' => 'required|in:technical,leadership,communication,management,soft_skills,domain',
            'planForm.action_type' => 'required|in:training,mentoring,project,certification,course,coaching,conference,reading',
            'planForm.start_date' => 'required|date',
            'planForm.target_date' => 'required|date|after:planForm.start_date',
            'planForm.resources_needed' => 'nullable|string|max:500',
            'planForm.budget' => 'nullable|numeric|min:0',
        ]);

        DevelopmentPlan::create([
            'employee_id' => $this->planForm['employee_id'],
            'title' => $this->planForm['title'],
            'description' => $this->planForm['description'],
            'skill_area' => $this->planForm['skill_area'],
            'action_type' => $this->planForm['action_type'],
            'start_date' => $this->planForm['start_date'],
            'target_date' => $this->planForm['target_date'],
            'resources_needed' => $this->planForm['resources_needed'],
            'budget' => $this->planForm['budget'] ?: null,
            'status' => 'planned',
            'progress_percentage' => 0,
            'created_by' => auth()->id(),
        ]);

        $this->showCreateModal = false;
        $this->dispatch('notify', type: 'success', message: 'Plan de développement créé.');
    }

    public function startPlan($planId)
    {
        $plan = DevelopmentPlan::findOrFail($planId);
        $plan->start();

        $this->dispatch('notify', type: 'success', message: 'Plan démarré.');
    }

    public function openProgressModal($planId)
    {
        $this->selectedPlan = DevelopmentPlan::findOrFail($planId);
        $this->progressPercentage = $this->selectedPlan->progress_percentage;
        $this->completionNotes = '';
        $this->showProgressModal = true;
    }

    public function updateProgress()
    {
        $this->validate([
            'progressPercentage' => 'required|integer|min:0|max:100',
        ]);

        $this->selectedPlan->updateProgress($this->progressPercentage);

        if ($this->progressPercentage >= 100 && $this->completionNotes) {
            $this->selectedPlan->complete($this->completionNotes);
        }

        $this->showProgressModal = false;
        $this->dispatch('notify', type: 'success', message: 'Progression mise à jour.');
    }

    public function completePlan($planId)
    {
        $plan = DevelopmentPlan::findOrFail($planId);
        $plan->complete();

        $this->dispatch('notify', type: 'success', message: 'Plan marqué comme complété.');
    }

    public function cancelPlan($planId)
    {
        $plan = DevelopmentPlan::findOrFail($planId);
        $plan->cancel();

        $this->dispatch('notify', type: 'success', message: 'Plan annulé.');
    }

    public function render()
    {
        $plans = DevelopmentPlan::query()
            ->with(['employee.department'])
            ->when($this->search, function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhereHas('employee', function ($eq) {
                      $eq->where('first_name', 'like', "%{$this->search}%")
                         ->orWhere('last_name', 'like', "%{$this->search}%");
                  });
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->skillAreaFilter, fn($q) => $q->where('skill_area', $this->skillAreaFilter))
            ->when($this->employeeFilter, fn($q) => $q->where('employee_id', $this->employeeFilter))
            ->orderBy('target_date')
            ->paginate(15);

        $employees = Employee::where('status', 'active')
            ->orderBy('last_name')
            ->get();

        // Stats
        $stats = [
            'total' => DevelopmentPlan::count(),
            'in_progress' => DevelopmentPlan::where('status', 'in_progress')->count(),
            'completed' => DevelopmentPlan::where('status', 'completed')->count(),
            'overdue' => DevelopmentPlan::overdue()->count(),
            'total_budget' => DevelopmentPlan::whereIn('status', ['planned', 'in_progress'])->sum('budget'),
        ];

        return view('hr::livewire.evaluations.development-plans-list', [
            'plans' => $plans,
            'employees' => $employees,
            'statuses' => DevelopmentPlan::STATUSES,
            'skillAreas' => DevelopmentPlan::SKILL_AREAS,
            'actionTypes' => DevelopmentPlan::ACTION_TYPES,
            'stats' => $stats,
        ])->layout('layouts.app');
    }
}
