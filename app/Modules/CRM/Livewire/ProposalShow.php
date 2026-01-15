<?php

namespace App\Modules\CRM\Livewire;

use Livewire\Component;
use App\Modules\CRM\Models\Proposal;

class ProposalShow extends Component
{
    public Proposal $proposal;
    public $showDeleteModal = false;

    public function mount(Proposal $proposal)
    {
        $this->proposal = $proposal->load(['contact', 'lines.product', 'creator']);
    }

    public function markAsSent()
    {
        $this->proposal->update(['status' => 'sent', 'sent_at' => now()]);
        $this->proposal->refresh();

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Devis {$this->proposal->reference} marqué comme envoyé."
        ]);
    }

    public function markAsAccepted()
    {
        $this->proposal->update(['status' => 'accepted', 'accepted_at' => now()]);
        $this->proposal->refresh();

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Devis {$this->proposal->reference} accepté avec succès !"
        ]);
    }

    public function markAsRefused()
    {
        $this->proposal->update(['status' => 'refused', 'rejected_at' => now()]);
        $this->proposal->refresh();

        $this->dispatch('toast', [
            'type' => 'warning',
            'message' => "Devis {$this->proposal->reference} marqué comme refusé."
        ]);
    }

    public function confirmDelete()
    {
        $this->showDeleteModal = true;
    }

    public function deleteProposal()
    {
        // Vérifier si le devis peut être supprimé
        if ($this->proposal->order || $this->proposal->invoice) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => "Ce devis ne peut pas être supprimé car il est lié à une commande ou facture."
            ]);
            $this->showDeleteModal = false;
            return;
        }

        $reference = $this->proposal->reference;
        $this->proposal->lines()->delete();
        $this->proposal->delete();

        session()->flash('success', "Devis {$reference} supprimé avec succès.");

        // Forcer la redirection via JavaScript pour éviter les problèmes de re-render
        $this->js('window.location.href = "' . route('crm.proposals.index') . '"');
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
    }

    public function render()
    {
        return view('crm::livewire.proposal-show')->layout('layouts.app');
    }
}
