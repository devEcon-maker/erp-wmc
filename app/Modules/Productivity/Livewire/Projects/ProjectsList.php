<?php

namespace App\Modules\Productivity\Livewire\Projects;

use App\Modules\Productivity\Models\Project;
use App\Modules\CRM\Models\Contact;
use App\Modules\HR\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectsList extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $managerId = '';
    public $contactId = '';
    public $billingType = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'managerId' => ['except' => ''],
        'contactId' => ['except' => ''],
        'billingType' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getProjectsProperty()
    {
        return Project::with(['contact', 'manager', 'members'])
            ->withCount('tasks')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->managerId, function ($query) {
                $query->where('manager_id', $this->managerId);
            })
            ->when($this->contactId, function ($query) {
                $query->where('contact_id', $this->contactId);
            })
            ->when($this->billingType, function ($query) {
                $query->where('billing_type', $this->billingType);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    public function getStatsProperty(): array
    {
        return [
            'total' => Project::count(),
            'active' => Project::active()->count(),
            'planning' => Project::planning()->count(),
            'completed' => Project::completed()->count(),
        ];
    }

    public function render()
    {
        return view('livewire.productivity.projects.projects-list', [
            'projects' => $this->projects,
            'stats' => $this->stats,
            'managers' => Employee::orderBy('last_name')->get(),
            'clients' => Contact::clients()->orderBy('company_name')->get(),
        ])->layout('layouts.app');
    }
}
