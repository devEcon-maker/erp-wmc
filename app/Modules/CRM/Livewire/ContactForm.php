<?php

namespace App\Modules\CRM\Livewire;

use Livewire\Component;
use App\Modules\CRM\Models\Contact;
use App\Models\User;
use Illuminate\Validation\Rule;

class ContactForm extends Component
{
    public ?Contact $contact = null;

    // Form fields
    public $type = 'prospect';
    public $company_name;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $mobile;
    public $address;
    public $city;
    public $postal_code;
    public $country = 'France';
    public $status = 'active';
    public $source;
    public $assigned_to;
    public $notes;

    public function mount(Contact $contact = null)
    {
        if ($contact && $contact->exists) {
            $this->contact = $contact;
            $this->fill($contact->toArray());
        } else {
            $this->contact = new Contact();
            $this->assigned_to = auth()->id();
        }
    }

    public function rules()
    {
        return [
            'type' => 'required|in:prospect,client,fournisseur',
            'company_name' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('contacts', 'email')->ignore($this->contact->id)],
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'source' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ];
    }

    public function save()
    {
        $this->validate();

        $isNew = !$this->contact->exists;

        $this->contact->fill($this->except('contact'));
        $this->contact->save();

        $message = $isNew
            ? "Contact {$this->contact->display_name} créé avec succès."
            : "Contact {$this->contact->display_name} mis à jour avec succès.";

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => $message
        ]);

        session()->flash('success', $message);

        return redirect()->route('crm.contacts.show', $this->contact);
    }

    public function render()
    {
        // Users with permission 'contacts.create' (or 'contacts.view' at least to be assigned)
        // I'll filter check permissions via Spatie.
        // Assuming users list is manageable.
        return view('crm::livewire.contact-form', [
            'users' => User::all(),
        ])->layout('layouts.app');
    }
}
