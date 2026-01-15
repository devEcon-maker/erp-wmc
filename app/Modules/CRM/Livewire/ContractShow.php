<?php

namespace App\Modules\CRM\Livewire;

use Livewire\Component;
use App\Modules\CRM\Models\Contract;
use App\Modules\CRM\Services\ContractService;

class ContractShow extends Component
{
    public Contract $contract;
    public $showDeleteModal = false;

    public function mount(Contract $contract)
    {
        $this->contract = $contract->load(['contact', 'lines.product', 'creator']);
    }

    public function activate(ContractService $service)
    {
        $service->activate($this->contract);
        $this->contract->refresh();

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Contrat {$this->contract->reference} activé avec succès."
        ]);
    }

    public function suspend()
    {
        $this->contract->update(['status' => 'suspended']);
        $this->contract->refresh();

        $this->dispatch('toast', [
            'type' => 'warning',
            'message' => "Contrat {$this->contract->reference} suspendu."
        ]);
    }

    public function terminate()
    {
        $this->contract->update(['status' => 'terminated', 'end_date' => now()]);
        $this->contract->refresh();

        $this->dispatch('toast', [
            'type' => 'warning',
            'message' => "Contrat {$this->contract->reference} résilié."
        ]);
    }

    public function confirmDelete()
    {
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
    }

    public function deleteContract()
    {
        $reference = $this->contract->reference;
        $this->contract->lines()->delete();
        $this->contract->delete();

        session()->flash('success', "Contrat {$reference} supprimé avec succès.");
        $this->js('window.location.href = "' . route('crm.contracts.index') . '"');
    }

    public function render()
    {
        return view('crm::livewire.contract-show')->layout('layouts.app');
    }
}
