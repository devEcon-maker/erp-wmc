<?php

namespace App\Modules\HR\Livewire\Evaluations;

use App\Modules\HR\Models\Evaluation;
use App\Modules\HR\Models\EmployeeObjective;
use Livewire\Component;

class MyEvaluations extends Component
{
    public $activeTab = 'evaluations'; // evaluations, objectives, history

    public function getEmployeeProperty()
    {
        return auth()->user()->employee;
    }

    public function render()
    {
        $pendingEvaluations = collect();
        $objectives = collect();
        $evaluationHistory = collect();

        if ($this->employee) {
            // Évaluations en attente (à compléter)
            $pendingEvaluations = Evaluation::query()
                ->where('employee_id', $this->employee->id)
                ->whereIn('status', ['pending', 'self_evaluation'])
                ->with('evaluationPeriod')
                ->orderByDesc('created_at')
                ->get();

            // Évaluations à faire en tant que manager
            $teamEvaluations = Evaluation::query()
                ->where('evaluator_id', $this->employee->user_id)
                ->where('status', 'manager_evaluation')
                ->with(['employee', 'evaluationPeriod'])
                ->orderByDesc('created_at')
                ->get();

            // Objectifs actifs
            $objectives = EmployeeObjective::query()
                ->where('employee_id', $this->employee->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->orderBy('due_date')
                ->get();

            // Historique des évaluations
            $evaluationHistory = Evaluation::query()
                ->where('employee_id', $this->employee->id)
                ->where('status', 'completed')
                ->with('evaluationPeriod')
                ->orderByDesc('completed_at')
                ->limit(10)
                ->get();
        }

        return view('hr::livewire.evaluations.my-evaluations', [
            'pendingEvaluations' => $pendingEvaluations,
            'teamEvaluations' => $teamEvaluations ?? collect(),
            'objectives' => $objectives,
            'evaluationHistory' => $evaluationHistory,
        ])->layout('layouts.app');
    }
}
