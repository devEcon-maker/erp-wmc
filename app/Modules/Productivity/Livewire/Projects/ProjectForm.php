<?php

namespace App\Modules\Productivity\Livewire\Projects;

use App\Modules\Productivity\Models\Project;
use App\Modules\Productivity\Services\ProjectService;
use App\Modules\CRM\Models\Contact;
use App\Modules\HR\Models\Employee;
use Livewire\Component;

class ProjectForm extends Component
{
    public ?Project $project = null;

    public $name = '';
    public $description = '';
    public $contact_id = '';
    public $manager_id = '';
    public $start_date = '';
    public $end_date = '';
    public $budget = '';
    public $status = 'planning';
    public $billing_type = 'non_billable';
    public $hourly_rate = '';

    // Membres du projet
    public $members = [];
    public $newMember = [
        'employee_id' => '',
        'role' => '',
        'hourly_rate' => '',
    ];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contact_id' => 'nullable|exists:contacts,id',
            'manager_id' => 'required|exists:employees,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
            'billing_type' => 'required|in:fixed,hourly,non_billable',
            'hourly_rate' => 'nullable|numeric|min:0',
        ];
    }

    public function mount(Project $project = null)
    {
        if ($project && $project->exists) {
            $this->project = $project;
            $this->name = $project->name;
            $this->description = $project->description ?? '';
            $this->contact_id = $project->contact_id ?? '';
            $this->manager_id = $project->manager_id;
            $this->start_date = $project->start_date?->format('Y-m-d') ?? '';
            $this->end_date = $project->end_date?->format('Y-m-d') ?? '';
            $this->budget = $project->budget ?? '';
            $this->status = $project->status;
            $this->billing_type = $project->billing_type;
            $this->hourly_rate = $project->hourly_rate ?? '';

            // Charger les membres
            foreach ($project->members as $member) {
                $this->members[] = [
                    'employee_id' => $member->id,
                    'employee_name' => $member->full_name,
                    'role' => $member->pivot->role ?? '',
                    'hourly_rate' => $member->pivot->hourly_rate ?? '',
                ];
            }
        }
    }

    public function addMember()
    {
        if (empty($this->newMember['employee_id'])) {
            return;
        }

        // Vérifier que l'employé n'est pas déjà membre
        $exists = collect($this->members)->where('employee_id', $this->newMember['employee_id'])->count() > 0;
        if ($exists) {
            $this->addError('newMember.employee_id', 'Cet employé est déjà membre du projet.');
            return;
        }

        $employee = Employee::find($this->newMember['employee_id']);
        if ($employee) {
            $this->members[] = [
                'employee_id' => $employee->id,
                'employee_name' => $employee->full_name,
                'role' => $this->newMember['role'],
                'hourly_rate' => $this->newMember['hourly_rate'],
            ];
        }

        $this->newMember = ['employee_id' => '', 'role' => '', 'hourly_rate' => ''];
    }

    public function removeMember($index)
    {
        unset($this->members[$index]);
        $this->members = array_values($this->members);
    }

    public function save(ProjectService $service)
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'contact_id' => $this->contact_id ?: null,
            'manager_id' => $this->manager_id,
            'start_date' => $this->start_date ?: null,
            'end_date' => $this->end_date ?: null,
            'budget' => $this->budget ?: null,
            'status' => $this->status,
            'billing_type' => $this->billing_type,
            'hourly_rate' => $this->hourly_rate ?: null,
        ];

        $membersData = collect($this->members)->map(function ($member) {
            return [
                'employee_id' => $member['employee_id'],
                'role' => $member['role'] ?: null,
                'hourly_rate' => $member['hourly_rate'] ?: null,
            ];
        })->toArray();

        if ($this->project && $this->project->exists) {
            $project = $service->update($this->project, $data, $membersData);
            $message = 'Projet mis à jour.';
        } else {
            $project = $service->create($data, $membersData);
            $message = 'Projet créé.';
        }

        return redirect()->route('productivity.projects.show', $project)
            ->with('success', $message);
    }

    public function render()
    {
        return view('livewire.productivity.projects.project-form', [
            'employees' => Employee::where('status', 'active')->orderBy('last_name')->get(),
            'clients' => Contact::clients()->orderBy('company_name')->get(),
        ])->layout('layouts.app');
    }
}
