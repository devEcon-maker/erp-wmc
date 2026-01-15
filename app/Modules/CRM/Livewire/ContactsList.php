<?php

namespace App\Modules\CRM\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Modules\CRM\Models\Contact;
use App\Models\User;

class ContactsList extends Component
{
    use WithPagination;

    public $search = '';
    public $type = '';
    public $status = '';
    public $assigned_to = '';

    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public $confirmingDeletion = false;
    public $contactIdToDelete;

    protected $queryString = ['search', 'type', 'status', 'assigned_to', 'sortField', 'sortDirection'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function confirmDeletion($id)
    {
        $this->confirmingDeletion = true;
        $this->contactIdToDelete = $id;
    }

    public function deleteContact()
    {
        $contact = Contact::find($this->contactIdToDelete);
        if ($contact) {
            $name = $contact->display_name;
            $contact->delete();

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Contact {$name} supprimé avec succès."
            ]);
        }
        $this->confirmingDeletion = false;
        $this->contactIdToDelete = null;
    }

    public function export()
    {
        return response()->streamDownload(function () {
            echo "ID,Nom,Entreprise,Email,Type,Statut,Source,Assigne A\n";
            $contacts = $this->getContactsQuery()->get();
            foreach ($contacts as $contact) {
                echo sprintf(
                    "%s,\"%s\",\"%s\",%s,%s,%s,%s,%s\n",
                    $contact->id,
                    $contact->full_name,
                    $contact->company_name,
                    $contact->email,
                    $contact->type,
                    $contact->status,
                    $contact->source,
                    $contact->user ? $contact->user->name : ''
                );
            }
        }, 'contacts-' . date('Y-m-d') . '.csv');
    }

    public function getContactsQuery()
    {
        return Contact::query()
            ->with('user')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhere('company_name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->assigned_to, fn($q) => $q->where('assigned_to', $this->assigned_to))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        return view('crm::livewire.contacts-list', [
            'contacts' => $this->getContactsQuery()->paginate(25),
            'users' => User::all(),
        ])->layout('layouts.app');
    }
}
