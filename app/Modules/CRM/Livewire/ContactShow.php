<?php

namespace App\Modules\CRM\Livewire;

use Livewire\Component;
use App\Modules\CRM\Models\Contact;

class ContactShow extends Component
{
    public Contact $contact;
    public $activeTab = 'infos';
    public $showDeleteModal = false;

    public function mount(Contact $contact)
    {
        $this->contact = $contact->load([
            'opportunities.stage',
            'opportunities.assignee',
            'orders.creator',
            'contracts.creator',
            'invoices',
        ]);
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function confirmDelete()
    {
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
    }

    public function delete()
    {
        $name = $this->contact->display_name;
        $this->contact->delete();

        session()->flash('success', "Contact {$name} supprimé avec succès.");
        $this->js('window.location.href = "' . route('crm.contacts.index') . '"');
    }

    public function convertToClient()
    {
        if ($this->contact->type === 'prospect') {
            $this->contact->update([
                'type' => 'client',
                'converted_at' => now(),
            ]);

            $this->contact->refresh();

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Prospect {$this->contact->display_name} converti en client avec succès."
            ]);
        }
    }

    public function render()
    {
        return view('crm::livewire.contact-show')->layout('layouts.app');
    }
}
