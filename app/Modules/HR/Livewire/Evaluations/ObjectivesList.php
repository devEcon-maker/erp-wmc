<?php

namespace App\Modules\HR\Livewire\Evaluations;

use App\Modules\HR\Models\EmployeeObjective;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Services\EvaluationService;
use Livewire\Component;
use Livewire\WithPagination;

class ObjectivesList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $employeeFilter = '';
    public $yearFilter = '';

    // Modal création
    public $showCreateModal = false;
    public $objectiveForm = [
        'employee_id' => '',
        'title' => '',
        'description' => '',
        'category' => 'performance',
        'priority' => 'medium',
        'start_date' => '',
        'due_date' => '',
        'target_value' => '',
        'unit' => '',
    ];

    // Modal mise à jour progression
    public $showProgressModal = false;
    public $selectedObjective = null;
    public $progressValue = 0;
    public $progressNotes = '';

    protected $queryString = ['search', 'statusFilter', 'yearFilter'];

    public function mount()
    {
        $this->yearFilter = date('Y');
        $this->objectiveForm['start_date'] = date('Y-m-d');
        $this->objectiveForm['due_date'] = date('Y-12-31');
    }

    public function openCreateModal()
    {
        $this->reset('objectiveForm');
        $this->objectiveForm['start_date'] = date('Y-m-d');
        $this->objectiveForm['due_date'] = date('Y-12-31');
        $this->objectiveForm['category'] = 'performance';
        $this->objectiveForm['priority'] = 'medium';
        $this->showCreateModal = true;
    }

    public function createObjective()
    {
        $this->validate([
            'objectiveForm.employee_id' => 'required|exists:employees,id',
            'objectiveForm.title' => 'required|string|max:255',
            'objectiveForm.description' => 'nullable|string|max:1000',
            'objectiveForm.category' => 'required|in:performance,development,project,behavioral',
            'objectiveForm.priority' => 'required|in:low,medium,high,critical',
            'objectiveForm.start_date' => 'required|date',
            'objectiveForm.due_date' => 'required|date|after:objectiveForm.start_date',
            'objectiveForm.target_value' => 'nullable|numeric',
            'objectiveForm.unit' => 'nullable|string|max:50',
        ]);

        $evaluationService = app(EvaluationService::class);
        $evaluationService->createObjective(
            (int) $this->objectiveForm['employee_id'],
            $this->objectiveForm['title'],
            $this->objectiveForm['description'],
            $this->objectiveForm['due_date'],
            $this->objectiveForm['category'],
            $this->objectiveForm['priority'],
            $this->objectiveForm['target_value'] ?: null,
            $this->objectiveForm['unit'] ?: null
        );

        $this->showCreateModal = false;
        $this->dispatch('notify', type: 'success', message: 'Objectif créé avec succès.');
    }

    public function openProgressModal($objectiveId)
    {
        $this->selectedObjective = EmployeeObjective::findOrFail($objectiveId);
        $this->progressValue = $this->selectedObjective->current_value ?? 0;
        $this->progressNotes = '';
        $this->showProgressModal = true;
    }

    public function updateProgress()
    {
        $this->validate([
            'progressValue' => 'required|numeric|min:0',
            'progressNotes' => 'nullable|string|max:500',
        ]);

        $this->selectedObjective->update([
            'current_value' => $this->progressValue,
            'progress_notes' => $this->progressNotes,
        ]);

        // Calculer le pourcentage de progression
        if ($this->selectedObjective->target_value > 0) {
            $percentage = min(100, ($this->progressValue / $this->selectedObjective->target_value) * 100);
            $this->selectedObjective->update(['progress_percentage' => $percentage]);

            // Marquer comme complété si 100%
            if ($percentage >= 100 && $this->selectedObjective->status !== 'completed') {
                $this->selectedObjective->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }
        }

        $this->showProgressModal = false;
        $this->dispatch('notify', type: 'success', message: 'Progression mise à jour.');
    }

    public function startObjective($objectiveId)
    {
        $objective = EmployeeObjective::findOrFail($objectiveId);
        $objective->update(['status' => 'in_progress']);

        $this->dispatch('notify', type: 'success', message: 'Objectif démarré.');
    }

    public function completeObjective($objectiveId)
    {
        $objective = EmployeeObjective::findOrFail($objectiveId);
        $objective->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);

        $this->dispatch('notify', type: 'success', message: 'Objectif marqué comme complété.');
    }

    public function cancelObjective($objectiveId)
    {
        $objective = EmployeeObjective::findOrFail($objectiveId);
        $objective->update(['status' => 'cancelled']);

        $this->dispatch('notify', type: 'success', message: 'Objectif annulé.');
    }

    public function render()
    {
        $objectives = EmployeeObjective::query()
            ->with(['employee.department'])
            ->when($this->search, function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhereHas('employee', function ($eq) {
                      $eq->where('first_name', 'like', "%{$this->search}%")
                         ->orWhere('last_name', 'like', "%{$this->search}%");
                  });
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->employeeFilter, fn($q) => $q->where('employee_id', $this->employeeFilter))
            ->when($this->yearFilter, fn($q) => $q->whereYear('due_date', $this->yearFilter))
            ->orderBy('due_date')
            ->paginate(15);

        $employees = Employee::where('status', 'active')
            ->orderBy('last_name')
            ->get();

        return view('hr::livewire.evaluations.objectives-list', [
            'objectives' => $objectives,
            'employees' => $employees,
            'statuses' => EmployeeObjective::STATUSES,
            'categories' => EmployeeObjective::CATEGORIES,
            'priorities' => EmployeeObjective::PRIORITIES,
        ])->layout('layouts.app');
    }
}
