<?php

namespace App\Modules\HR\Livewire\Evaluations;

use App\Modules\HR\Models\EvaluationPeriod;
use App\Modules\HR\Models\EvaluationTemplate;
use App\Modules\HR\Services\EvaluationService;
use Livewire\Component;
use Livewire\WithPagination;

class EvaluationPeriodsList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $yearFilter = '';

    // Modal création
    public $showCreateModal = false;
    public $periodForm = [
        'name' => '',
        'type' => 'annual',
        'year' => '',
        'start_date' => '',
        'end_date' => '',
        'evaluation_template_id' => '',
    ];

    protected $queryString = ['search', 'statusFilter', 'yearFilter'];

    public function mount()
    {
        $this->yearFilter = date('Y');
        $this->periodForm['year'] = date('Y');
    }

    public function openCreateModal()
    {
        $this->reset('periodForm');
        $this->periodForm['year'] = date('Y');
        $this->periodForm['type'] = 'annual';
        $this->showCreateModal = true;
    }

    public function createPeriod()
    {
        $this->validate([
            'periodForm.name' => 'required|string|max:255',
            'periodForm.type' => 'required|in:annual,semi_annual,quarterly,probation',
            'periodForm.start_date' => 'required|date',
            'periodForm.end_date' => 'required|date|after:periodForm.start_date',
            'periodForm.evaluation_template_id' => 'required|exists:evaluation_templates,id',
        ]);

        $evaluationService = app(EvaluationService::class);
        $evaluationService->createPeriod([
            'name' => $this->periodForm['name'],
            'type' => $this->periodForm['type'],
            'start_date' => $this->periodForm['start_date'],
            'end_date' => $this->periodForm['end_date'],
            'evaluation_start_date' => $this->periodForm['start_date'],
            'evaluation_end_date' => $this->periodForm['end_date'],
            'evaluation_template_id' => $this->periodForm['evaluation_template_id'],
        ]);

        $this->showCreateModal = false;
        $this->dispatch('notify', type: 'success', message: 'Période d\'évaluation créée.');
    }

    public function launchCampaign($periodId)
    {
        $period = EvaluationPeriod::findOrFail($periodId);

        if ($period->status !== 'draft') {
            $this->dispatch('notify', type: 'error', message: 'Cette période ne peut pas être lancée.');
            return;
        }

        if (!$period->evaluation_template_id) {
            $this->dispatch('notify', type: 'error', message: 'Aucun template d\'évaluation associé à cette période.');
            return;
        }

        $evaluationService = app(EvaluationService::class);
        $results = $evaluationService->launchCampaign($period, $period->evaluation_template_id);

        $this->dispatch('notify', type: 'success', message: "{$results['created']} évaluations créées.");
    }

    public function closePeriod($periodId)
    {
        $period = EvaluationPeriod::findOrFail($periodId);
        $period->update(['status' => 'closed']);

        $this->dispatch('notify', type: 'success', message: 'Période clôturée.');
    }

    public function deletePeriod($periodId)
    {
        $period = EvaluationPeriod::findOrFail($periodId);

        if ($period->status !== 'draft') {
            $this->dispatch('notify', type: 'error', message: 'Seules les périodes en brouillon peuvent être supprimées.');
            return;
        }

        $period->delete();
        $this->dispatch('notify', type: 'success', message: 'Période supprimée.');
    }

    public function render()
    {
        $periods = EvaluationPeriod::query()
            ->withCount('evaluations')
            ->with('template')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->yearFilter, fn($q) => $q->whereYear('start_date', $this->yearFilter))
            ->orderByDesc('start_date')
            ->paginate(12);

        $templates = EvaluationTemplate::where('is_active', true)->get();

        return view('hr::livewire.evaluations.evaluation-periods-list', [
            'periods' => $periods,
            'templates' => $templates,
            'statuses' => EvaluationPeriod::STATUSES,
            'types' => EvaluationPeriod::TYPES,
        ])->layout('layouts.app');
    }
}
