<?php

namespace App\Modules\HR\Livewire\Evaluations;

use App\Modules\HR\Models\Evaluation;
use App\Modules\HR\Models\EvaluationScore;
use App\Modules\HR\Services\EvaluationService;
use Livewire\Component;

class EvaluationForm extends Component
{
    public Evaluation $evaluation;

    public $scores = [];
    public $comments = [];
    public $generalComments = '';
    public $strengths = '';
    public $improvements = '';
    public $recommendations = '';

    public $isSelfEvaluation = false;

    public function mount(Evaluation $evaluation)
    {
        $this->evaluation = $evaluation->load([
            'employee',
            'evaluationPeriod.template.criteria',
            'scores',
        ]);

        $this->isSelfEvaluation = $this->evaluation->status === 'self_evaluation'
            && auth()->user()->employee?->id === $this->evaluation->employee_id;

        // Charger les scores existants
        foreach ($this->evaluation->scores as $score) {
            if ($this->isSelfEvaluation) {
                $this->scores[$score->evaluation_criteria_id] = $score->self_score;
                $this->comments[$score->evaluation_criteria_id] = $score->self_comment;
            } else {
                $this->scores[$score->evaluation_criteria_id] = $score->manager_score;
                $this->comments[$score->evaluation_criteria_id] = $score->manager_comment;
            }
        }

        // Initialiser les critères manquants
        foreach ($this->evaluation->evaluationPeriod->template->criteria as $criterion) {
            if (!isset($this->scores[$criterion->id])) {
                $this->scores[$criterion->id] = null;
                $this->comments[$criterion->id] = '';
            }
        }

        // Charger les commentaires généraux
        if ($this->isSelfEvaluation) {
            $this->generalComments = $this->evaluation->self_comments ?? '';
        } else {
            $this->generalComments = $this->evaluation->manager_comments ?? '';
            $this->strengths = $this->evaluation->strengths ?? '';
            $this->improvements = $this->evaluation->improvements ?? '';
            $this->recommendations = $this->evaluation->recommendations ?? '';
        }
    }

    public function save()
    {
        $this->validate([
            'scores.*' => 'nullable|integer|min:1|max:5',
            'comments.*' => 'nullable|string|max:1000',
            'generalComments' => 'nullable|string|max:2000',
        ]);

        $evaluationService = app(EvaluationService::class);

        $scoreData = [];
        foreach ($this->scores as $criteriaId => $score) {
            if ($score !== null) {
                $scoreData[$criteriaId] = [
                    'score' => $score,
                    'comment' => $this->comments[$criteriaId] ?? null,
                ];
            }
        }

        $evaluationService->saveScores(
            $this->evaluation,
            $scoreData,
            $this->isSelfEvaluation ? 'self' : 'manager'
        );

        // Sauvegarder les commentaires généraux
        $updateData = $this->isSelfEvaluation
            ? ['self_comments' => $this->generalComments]
            : [
                'manager_comments' => $this->generalComments,
                'strengths' => $this->strengths,
                'improvements' => $this->improvements,
                'recommendations' => $this->recommendations,
            ];

        $this->evaluation->update($updateData);

        $this->dispatch('notify', type: 'success', message: 'Évaluation sauvegardée.');
    }

    public function submit()
    {
        // Vérifier que tous les critères requis sont remplis
        $template = $this->evaluation->evaluationPeriod->template;
        $requiredCriteria = $template->criteria->where('is_required', true);

        foreach ($requiredCriteria as $criterion) {
            if (empty($this->scores[$criterion->id])) {
                $this->dispatch('notify', type: 'error', message: "Veuillez noter le critère: {$criterion->name}");
                return;
            }
        }

        $this->save();

        $evaluationService = app(EvaluationService::class);
        $evaluationService->submitEvaluation($this->evaluation, $this->isSelfEvaluation ? 'self' : 'manager');

        $this->dispatch('notify', type: 'success', message: 'Évaluation soumise avec succès.');

        return redirect()->route('hr.evaluations.my-evaluations');
    }

    public function render()
    {
        $criteria = $this->evaluation->evaluationPeriod->template->criteria()
            ->orderBy('order')
            ->get()
            ->groupBy('category');

        return view('hr::livewire.evaluations.evaluation-form', [
            'criteria' => $criteria,
            'categories' => [
                'technical' => 'Compétences techniques',
                'behavioral' => 'Compétences comportementales',
                'goals' => 'Objectifs',
                'leadership' => 'Leadership',
                'competencies' => 'Compétences',
            ],
        ])->layout('layouts.app');
    }
}
