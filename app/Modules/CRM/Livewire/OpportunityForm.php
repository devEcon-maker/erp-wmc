<?php

namespace App\Modules\CRM\Livewire;

use App\Modules\CRM\Models\Opportunity;
use App\Modules\CRM\Models\OpportunityStage;
use App\Modules\CRM\Models\Contact;
use App\Models\User;
use Livewire\Component;

class OpportunityForm extends Component
{
    public ?Opportunity $opportunity = null;
    public bool $isEdit = false;

    public $contact_id = '';
    public $title = '';
    public $description = '';
    public $amount = 0;
    public $stage_id = '';
    public $probability = 10;
    public $expected_close_date = '';
    public $assigned_to = '';

    public $stages;
    public $contacts;
    public $users;

    protected function rules()
    {
        return [
            'contact_id' => 'required|integer|exists:contacts,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'stage_id' => 'required|integer|exists:opportunity_stages,id',
            'probability' => 'required|integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'assigned_to' => 'nullable|integer|exists:users,id',
        ];
    }

    protected $messages = [
        'contact_id.required' => 'Veuillez selectionner un contact.',
        'title.required' => 'Le titre est obligatoire.',
        'amount.required' => 'Le montant est obligatoire.',
        'amount.min' => 'Le montant doit etre positif.',
        'stage_id.required' => 'Veuillez selectionner une etape.',
        'probability.min' => 'La probabilite doit etre entre 0 et 100.',
        'probability.max' => 'La probabilite doit etre entre 0 et 100.',
    ];

    public function mount(Opportunity $opportunity = null)
    {
        $this->stages = OpportunityStage::orderBy('order')->get();
        $this->contacts = Contact::orderBy('company_name')->get();
        $this->users = User::where('is_active', true)->get();

        if ($opportunity && $opportunity->exists) {
            $this->opportunity = $opportunity;
            $this->isEdit = true;
            $this->contact_id = $opportunity->contact_id;
            $this->title = $opportunity->title;
            $this->description = $opportunity->description ?? '';
            $this->amount = $opportunity->amount;
            $this->stage_id = $opportunity->stage_id;
            $this->probability = $opportunity->probability;
            $this->expected_close_date = $opportunity->expected_close_date?->format('Y-m-d') ?? '';
            $this->assigned_to = $opportunity->assigned_to ?? '';
        } else {
            $this->stage_id = $this->stages->first()->id ?? '';
            $this->probability = $this->stages->first()->probability ?? 10;
            $this->assigned_to = auth()->id();
        }
    }

    public function updatedStageId($value)
    {
        $stage = $this->stages->find($value);
        if ($stage) {
            $this->probability = $stage->probability;
        }
    }

    public function save()
    {
        $validated = $this->validate();

        // Nettoyer les valeurs vides
        $validated['assigned_to'] = $validated['assigned_to'] ?: null;
        $validated['expected_close_date'] = $validated['expected_close_date'] ?: null;
        $validated['description'] = $validated['description'] ?: null;

        if ($this->isEdit && $this->opportunity) {
            $this->opportunity->update($validated);

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Opportunite mise a jour avec succes.'
            ]);

            session()->flash('success', 'Opportunite mise a jour avec succes.');
        } else {
            $opportunity = Opportunity::create($validated);

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Opportunite creee avec succes.'
            ]);

            session()->flash('success', 'Opportunite creee avec succes.');
        }

        return redirect()->route('crm.opportunities.index');
    }

    public function render()
    {
        return view('crm::livewire.opportunity-form')->layout('layouts.app');
    }
}
