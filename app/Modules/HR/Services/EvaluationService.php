<?php

namespace App\Modules\HR\Services;

use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\EvaluationPeriod;
use App\Modules\HR\Models\EvaluationTemplate;
use App\Modules\HR\Models\Evaluation;
use App\Modules\HR\Models\EvaluationScore;
use App\Modules\HR\Models\EvaluationCriteria;
use App\Modules\HR\Models\FeedbackRequest;
use App\Modules\HR\Models\EmployeeObjective;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class EvaluationService
{
    /**
     * Creer une nouvelle periode d'evaluation
     */
    public function createPeriod(array $data): EvaluationPeriod
    {
        return EvaluationPeriod::create([
            'name' => $data['name'],
            'type' => $data['type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'evaluation_start_date' => $data['evaluation_start_date'],
            'evaluation_end_date' => $data['evaluation_end_date'],
            'description' => $data['description'] ?? null,
            'evaluation_template_id' => $data['evaluation_template_id'] ?? null,
            'status' => 'draft',
        ]);
    }

    /**
     * Lancer une campagne d'evaluation
     */
    public function launchCampaign(EvaluationPeriod $period, int $templateId, ?array $employeeIds = null): array
    {
        $template = EvaluationTemplate::findOrFail($templateId);

        // Selectionner les employes
        $employees = $employeeIds
            ? Employee::whereIn('id', $employeeIds)->active()->get()
            : Employee::active()->get();

        $results = ['created' => 0, 'errors' => []];

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                try {
                    // Creer l'auto-evaluation si requise
                    if ($template->include_self_evaluation) {
                        $this->createEvaluation($period, $template, $employee, $employee, 'self');
                        $results['created']++;
                    }

                    // Creer l'evaluation manager si requise
                    if ($template->include_manager_evaluation && $employee->manager_id) {
                        $this->createEvaluation($period, $template, $employee, $employee->manager, 'manager');
                        $results['created']++;
                    }

                    // Creer les demandes de feedback 360 si requis
                    if ($template->include_peer_evaluation) {
                        $this->createPeerFeedbackRequests($period, $employee);
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'employee' => $employee->full_name,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            // Ouvrir la periode
            $period->open();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    /**
     * Creer une evaluation
     */
    public function createEvaluation(
        EvaluationPeriod $period,
        EvaluationTemplate $template,
        Employee $employee,
        Employee $evaluator,
        string $evaluatorType
    ): Evaluation {
        // Verifier si une evaluation existe deja
        $existing = Evaluation::where('employee_id', $employee->id)
            ->where('evaluation_period_id', $period->id)
            ->where('evaluator_id', $evaluator->id)
            ->where('evaluator_type', $evaluatorType)
            ->first();

        if ($existing) {
            return $existing;
        }

        $evaluation = Evaluation::create([
            'employee_id' => $employee->id,
            'evaluation_period_id' => $period->id,
            'evaluation_template_id' => $template->id,
            'evaluator_id' => $evaluator->id,
            'evaluator_type' => $evaluatorType,
            'status' => 'draft',
        ]);

        // Creer les scores vides pour chaque critere
        foreach ($template->criteria as $criterion) {
            EvaluationScore::create([
                'evaluation_id' => $evaluation->id,
                'evaluation_criteria_id' => $criterion->id,
                'score' => null,
            ]);
        }

        return $evaluation;
    }

    /**
     * Creer les demandes de feedback pour une evaluation 360
     */
    public function createPeerFeedbackRequests(EvaluationPeriod $period, Employee $employee): void
    {
        // Obtenir les collegues du meme departement
        $peers = Employee::where('department_id', $employee->department_id)
            ->where('id', '!=', $employee->id)
            ->active()
            ->take(3) // Limiter a 3 collegues
            ->get();

        foreach ($peers as $peer) {
            FeedbackRequest::firstOrCreate([
                'employee_id' => $employee->id,
                'evaluation_period_id' => $period->id,
                'requested_from_id' => $peer->id,
            ], [
                'relationship' => 'peer',
                'status' => 'pending',
                'is_anonymous' => true,
            ]);
        }

        // Subordonnes directs
        $subordinates = Employee::where('manager_id', $employee->id)->active()->get();
        foreach ($subordinates as $sub) {
            FeedbackRequest::firstOrCreate([
                'employee_id' => $employee->id,
                'evaluation_period_id' => $period->id,
                'requested_from_id' => $sub->id,
            ], [
                'relationship' => 'subordinate',
                'status' => 'pending',
                'is_anonymous' => true,
            ]);
        }
    }

    /**
     * Enregistrer les scores d'une evaluation
     */
    public function saveScores(Evaluation $evaluation, array $scores): void
    {
        foreach ($scores as $criteriaId => $data) {
            EvaluationScore::where('evaluation_id', $evaluation->id)
                ->where('evaluation_criteria_id', $criteriaId)
                ->update([
                    'score' => $data['score'] ?? null,
                    'comments' => $data['comments'] ?? null,
                ]);
        }

        // Mettre a jour le statut si en brouillon
        if ($evaluation->status === 'draft') {
            $evaluation->update(['status' => 'in_progress']);
        }
    }

    /**
     * Soumettre une evaluation
     */
    public function submitEvaluation(Evaluation $evaluation): void
    {
        if (!$evaluation->canSubmit()) {
            throw new \Exception("Tous les criteres doivent etre evalues avant la soumission");
        }

        $evaluation->submit();
    }

    /**
     * Valider une evaluation
     */
    public function validateEvaluation(Evaluation $evaluation, \App\Models\User $validator): void
    {
        if ($evaluation->status !== 'submitted') {
            throw new \Exception("Seules les evaluations soumises peuvent etre validees");
        }

        $evaluation->validate($validator);
    }

    /**
     * Obtenir le resume d'evaluation d'un employe
     */
    public function getEmployeeEvaluationSummary(Employee $employee, ?int $periodId = null): array
    {
        $query = Evaluation::where('employee_id', $employee->id)
            ->where('status', 'validated');

        if ($periodId) {
            $query->where('evaluation_period_id', $periodId);
        }

        $evaluations = $query->with(['evaluator', 'evaluationPeriod'])->get();

        if ($evaluations->isEmpty()) {
            return [
                'average_score' => null,
                'evaluations_count' => 0,
                'self_score' => null,
                'manager_score' => null,
                'peer_average' => null,
            ];
        }

        $selfEval = $evaluations->where('evaluator_type', 'self')->first();
        $managerEval = $evaluations->where('evaluator_type', 'manager')->first();
        $peerEvals = $evaluations->where('evaluator_type', 'peer');

        return [
            'average_score' => round($evaluations->avg('overall_score'), 2),
            'evaluations_count' => $evaluations->count(),
            'self_score' => $selfEval?->overall_score,
            'manager_score' => $managerEval?->overall_score,
            'peer_average' => $peerEvals->isNotEmpty() ? round($peerEvals->avg('overall_score'), 2) : null,
            'overall_rating' => $this->calculateOverallRating($evaluations),
            'last_evaluation' => $evaluations->sortByDesc('validated_at')->first(),
        ];
    }

    /**
     * Calculer la note globale
     */
    private function calculateOverallRating(Collection $evaluations): string
    {
        $avgScore = $evaluations->avg('overall_score');

        return match (true) {
            $avgScore >= 4.5 => 'A',
            $avgScore >= 3.5 => 'B',
            $avgScore >= 2.5 => 'C',
            $avgScore >= 1.5 => 'D',
            default => 'E',
        };
    }

    /**
     * Obtenir les objectifs d'un employe pour une periode
     */
    public function getEmployeeObjectives(Employee $employee, ?int $periodId = null): Collection
    {
        $query = EmployeeObjective::where('employee_id', $employee->id);

        if ($periodId) {
            $query->where('evaluation_period_id', $periodId);
        }

        return $query->orderBy('due_date')->get();
    }

    /**
     * Creer un objectif
     */
    public function createObjective(Employee $employee, array $data): EmployeeObjective
    {
        return EmployeeObjective::create([
            'employee_id' => $employee->id,
            'evaluation_period_id' => $data['evaluation_period_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? null,
            'type' => $data['type'] ?? 'quantitative',
            'metric' => $data['metric'] ?? null,
            'target_value' => $data['target_value'] ?? null,
            'unit' => $data['unit'] ?? null,
            'weight' => $data['weight'] ?? 1,
            'start_date' => $data['start_date'],
            'due_date' => $data['due_date'],
            'status' => 'in_progress',
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Mettre a jour la progression d'un objectif
     */
    public function updateObjectiveProgress(EmployeeObjective $objective, float $currentValue): void
    {
        $objective->current_value = $currentValue;
        $objective->updateProgress();
    }

    /**
     * Obtenir les statistiques d'evaluation d'une periode
     */
    public function getPeriodStats(EvaluationPeriod $period): array
    {
        $evaluations = Evaluation::where('evaluation_period_id', $period->id)->get();

        $submitted = $evaluations->whereIn('status', ['submitted', 'validated'])->count();
        $total = $evaluations->count();

        return [
            'total_evaluations' => $total,
            'submitted' => $submitted,
            'pending' => $total - $submitted,
            'completion_rate' => $total > 0 ? round(($submitted / $total) * 100, 2) : 0,
            'average_score' => round($evaluations->where('status', 'validated')->avg('overall_score') ?? 0, 2),
            'by_rating' => $evaluations->where('status', 'validated')
                ->groupBy('overall_rating')
                ->map->count(),
        ];
    }

    /**
     * Cloturer une periode d'evaluation
     */
    public function closePeriod(EvaluationPeriod $period): void
    {
        // Verifier que toutes les evaluations sont validees ou soumises
        $pending = Evaluation::where('evaluation_period_id', $period->id)
            ->whereIn('status', ['draft', 'in_progress'])
            ->count();

        if ($pending > 0) {
            throw new \Exception("{$pending} evaluations sont encore en attente");
        }

        $period->close();
    }
}
