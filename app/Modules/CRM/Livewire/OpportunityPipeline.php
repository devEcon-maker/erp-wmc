<?php

namespace App\Modules\CRM\Livewire;

use App\Modules\CRM\Models\Opportunity;
use App\Modules\CRM\Models\OpportunityStage;
use Livewire\Component;

class OpportunityPipeline extends Component
{
    public $stages;
    public $showDeleteModal = false;
    public $opportunityToDelete = null;

    public function mount()
    {
        $this->stages = OpportunityStage::orderBy('order')->get();
    }

    public function updateStage($opportunityId, $stageId)
    {
        $opportunity = Opportunity::find($opportunityId);
        $stage = OpportunityStage::find($stageId);

        if ($opportunity && $stage) {
            $oldStage = $opportunity->stage->name;
            $opportunity->stage_id = $stageId;
            $opportunity->probability = $stage->probability;

            // Handle Won/Lost status based on stage name
            if ($stage->name === 'Gagnée') {
                $opportunity->won_at = now();
                $opportunity->lost_at = null;
            } elseif ($stage->name === 'Perdue') {
                $opportunity->lost_at = now();
                $opportunity->won_at = null;
            } else {
                $opportunity->won_at = null;
                $opportunity->lost_at = null;
            }

            $opportunity->save();

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Opportunité déplacée de {$oldStage} vers {$stage->name}"
            ]);

            // Recharger la page pour mettre à jour les stats
            return $this->redirect(route('crm.opportunities.index'), navigate: true);
        }
    }

    public function confirmDelete($opportunityId)
    {
        $this->opportunityToDelete = Opportunity::find($opportunityId);
        $this->showDeleteModal = true;
    }

    public function deleteOpportunity()
    {
        if ($this->opportunityToDelete) {
            $title = $this->opportunityToDelete->title;
            $this->opportunityToDelete->delete();

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Opportunité \"{$title}\" supprimée avec succès"
            ]);

            $this->showDeleteModal = false;
            $this->opportunityToDelete = null;

            return $this->redirect(route('crm.opportunities.index'), navigate: true);
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->opportunityToDelete = null;
    }

    public function getOpportunitiesForStage($stageId)
    {
        return Opportunity::with('contact', 'assignee')
            ->where('stage_id', $stageId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getStatsProperty()
    {
        $opportunities = Opportunity::all();
        return [
            'total_count' => $opportunities->count(),
            'total_amount' => $opportunities->sum('amount'),
            'weighted_amount' => $opportunities->sum(function ($opp) {
                return $opp->amount * ($opp->probability / 100);
            }),
        ];
    }

    public function render()
    {
        return view('crm::livewire.opportunity-pipeline', [
            'stats' => $this->stats,
            'stages_list' => $this->stages // renaming to avoid conflict if any
        ])->layout('layouts.app');
    }
}
