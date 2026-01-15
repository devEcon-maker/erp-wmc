<?php

namespace App\Modules\HR\Livewire\Evaluations;

use App\Modules\HR\Models\EvaluationPeriod;
use App\Modules\HR\Models\Evaluation;
use Livewire\Component;
use Livewire\WithPagination;

class EvaluationPeriodShow extends Component
{
    use WithPagination;

    public EvaluationPeriod $evaluationPeriod;

    public $search = '';
    public $statusFilter = '';
    public $departmentFilter = '';

    public $stats = [];

    public function mount(EvaluationPeriod $evaluationPeriod)
    {
        $this->evaluationPeriod = $evaluationPeriod->load('template');
        $this->calculateStats();
    }

    private function calculateStats()
    {
        $evaluations = $this->evaluationPeriod->evaluations;

        $this->stats = [
            'total' => $evaluations->count(),
            'pending' => $evaluations->where('status', 'pending')->count(),
            'self_evaluation' => $evaluations->where('status', 'self_evaluation')->count(),
            'manager_evaluation' => $evaluations->where('status', 'manager_evaluation')->count(),
            'completed' => $evaluations->where('status', 'completed')->count(),
            'average_score' => $evaluations->where('status', 'completed')->avg('final_score'),
            'completion_rate' => $evaluations->count() > 0
                ? round($evaluations->where('status', 'completed')->count() / $evaluations->count() * 100)
                : 0,
        ];
    }

    public function sendReminder($evaluationId)
    {
        // TODO: Implémenter l'envoi de rappel
        $this->dispatch('notify', type: 'info', message: 'Rappel envoyé.');
    }

    public function render()
    {
        $evaluations = Evaluation::query()
            ->where('evaluation_period_id', $this->evaluationPeriod->id)
            ->with(['employee.department', 'evaluator'])
            ->when($this->search, function ($q) {
                $q->whereHas('employee', function ($eq) {
                    $eq->where('first_name', 'like', "%{$this->search}%")
                       ->orWhere('last_name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->departmentFilter, function ($q) {
                $q->whereHas('employee', fn($eq) => $eq->where('department_id', $this->departmentFilter));
            })
            ->orderBy('status')
            ->paginate(20);

        $departments = \App\Modules\HR\Models\Department::orderBy('name')->get();

        return view('hr::livewire.evaluations.evaluation-period-show', [
            'evaluations' => $evaluations,
            'departments' => $departments,
        ])->layout('layouts.app');
    }
}
