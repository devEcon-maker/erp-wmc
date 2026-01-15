<?php

namespace App\Modules\CRM\Livewire;

use App\Modules\CRM\Models\Opportunity;
use App\Modules\CRM\Models\OpportunityStage;
use Livewire\Component;

class OpportunityShow extends Component
{
    public Opportunity $opportunity;
    public $stages;
    public $showDeleteModal = false;

    public function mount(Opportunity $opportunity)
    {
        $this->opportunity = $opportunity->load('contact', 'stage', 'assignee');
        $this->stages = OpportunityStage::orderBy('order')->get();
    }

    public function markAsWon()
    {
        $wonStage = OpportunityStage::where('name', 'Gagnée')->first();
        if ($wonStage) {
            $this->opportunity->update([
                'stage_id' => $wonStage->id,
                'probability' => 100,
                'won_at' => now(),
                'lost_at' => null,
            ]);

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Opportunité marquée comme gagnée !'
            ]);

            $this->opportunity->refresh()->load('contact', 'stage', 'assignee');
        }
    }

    public function markAsLost()
    {
        $lostStage = OpportunityStage::where('name', 'Perdue')->first();
        if ($lostStage) {
            $this->opportunity->update([
                'stage_id' => $lostStage->id,
                'probability' => 0,
                'lost_at' => now(),
                'won_at' => null,
            ]);

            $this->dispatch('toast', [
                'type' => 'warning',
                'message' => 'Opportunité marquée comme perdue.'
            ]);

            $this->opportunity->refresh()->load('contact', 'stage', 'assignee');
        }
    }

    public function confirmDelete()
    {
        $this->showDeleteModal = true;
    }

    public function deleteOpportunity()
    {
        $title = $this->opportunity->title;
        $this->opportunity->delete();

        session()->flash('success', "Opportunité \"{$title}\" supprimée avec succès");
        $this->js('window.location.href = "' . route('crm.opportunities.index') . '"');
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
    }

    public function render()
    {
        return view('crm::livewire.opportunity-show')->layout('layouts.app');
    }
}
