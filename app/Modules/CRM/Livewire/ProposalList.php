<?php

namespace App\Modules\CRM\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Modules\CRM\Models\Proposal;

class ProposalList extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $showDeleteModal = false;
    public $proposalToDelete = null;

    protected $queryString = ['search', 'status'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getProposalsProperty()
    {
        return Proposal::query()
            ->with(['contact', 'creator', 'order', 'invoice'])
            ->when($this->search, function ($query) {
                $query->where('reference', 'like', '%' . $this->search . '%')
                    ->orWhereHas('contact', function ($q) {
                        $q->where('first_name', 'like', '%' . $this->search . '%')
                            ->orWhere('last_name', 'like', '%' . $this->search . '%')
                            ->orWhere('company_name', 'like', '%' . $this->search . '%');
                    });
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    public function getStatsProperty()
    {
        return [
            'total' => Proposal::count(),
            'draft' => Proposal::where('status', 'draft')->count(),
            'sent' => Proposal::where('status', 'sent')->count(),
            'accepted' => Proposal::where('status', 'accepted')->count(),
            'refused' => Proposal::where('status', 'refused')->count(),
        ];
    }

    public function markAsSent(Proposal $proposal)
    {
        if ($proposal->status !== 'draft') {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => "Seuls les devis en brouillon peuvent être envoyés."
            ]);
            return;
        }

        $proposal->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Devis {$proposal->reference} marqué comme envoyé."
        ]);
    }

    public function markAsAccepted(Proposal $proposal)
    {
        if (!in_array($proposal->status, ['draft', 'sent'])) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => "Ce devis ne peut pas être accepté."
            ]);
            return;
        }

        $proposal->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Devis {$proposal->reference} accepté avec succès !"
        ]);
    }

    public function markAsRefused(Proposal $proposal)
    {
        if (!in_array($proposal->status, ['draft', 'sent'])) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => "Ce devis ne peut pas être refusé."
            ]);
            return;
        }

        $proposal->update([
            'status' => 'refused',
            'rejected_at' => now(),
        ]);

        $this->dispatch('toast', [
            'type' => 'warning',
            'message' => "Devis {$proposal->reference} marqué comme refusé."
        ]);
    }

    public function convertToOrder(Proposal $proposal)
    {
        try {
            $order = $proposal->convertToOrder();
            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Bon de commande {$order->reference} créé avec succès."
            ]);
        } catch (\Exception $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function convertToInvoice(Proposal $proposal)
    {
        try {
            $invoice = $proposal->convertToInvoice();
            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Facture {$invoice->reference} créée avec succès."
            ]);
        } catch (\Exception $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function confirmDelete($proposalId)
    {
        $this->proposalToDelete = Proposal::find($proposalId);
        $this->showDeleteModal = true;
    }

    public function deleteProposal()
    {
        if ($this->proposalToDelete) {
            // Vérifier si le devis peut être supprimé
            if ($this->proposalToDelete->order || $this->proposalToDelete->invoice) {
                $this->dispatch('toast', [
                    'type' => 'error',
                    'message' => "Ce devis ne peut pas être supprimé car il est lié à une commande ou facture."
                ]);
                $this->showDeleteModal = false;
                $this->proposalToDelete = null;
                return;
            }

            $reference = $this->proposalToDelete->reference;
            $this->proposalToDelete->lines()->delete();
            $this->proposalToDelete->delete();

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Devis {$reference} supprimé avec succès."
            ]);

            $this->showDeleteModal = false;
            $this->proposalToDelete = null;
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->proposalToDelete = null;
    }

    public function render()
    {
        return view('crm::livewire.proposal-list', [
            'proposals' => $this->proposals,
            'stats' => $this->stats,
        ])->layout('layouts.app');
    }
}
