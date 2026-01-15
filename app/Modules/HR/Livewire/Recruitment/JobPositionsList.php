<?php

namespace App\Modules\HR\Livewire\Recruitment;

use App\Modules\HR\Models\JobPosition;
use App\Modules\HR\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;

class JobPositionsList extends Component
{
    use WithPagination;

    public $status = '';
    public $departmentId = '';
    public $type = '';
    public $search = '';

    protected $queryString = [
        'status' => ['except' => ''],
        'departmentId' => ['except' => ''],
        'type' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function getPositionsProperty()
    {
        return JobPosition::with(['department'])
            ->withCount('applications')
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->departmentId, fn($q) => $q->where('department_id', $this->departmentId))
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->when($this->search, fn($q) => $q->where('title', 'like', '%' . $this->search . '%'))
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function getStatsProperty(): array
    {
        return [
            'draft' => JobPosition::draft()->count(),
            'published' => JobPosition::published()->count(),
            'closed' => JobPosition::closed()->count(),
            'total_applications' => \App\Modules\HR\Models\JobApplication::count(),
        ];
    }

    public function publish($id)
    {
        $position = JobPosition::find($id);
        if ($position && $position->publish()) {
            $this->dispatch('notify', message: 'Poste publié.', type: 'success');
        }
    }

    public function unpublish($id)
    {
        $position = JobPosition::find($id);
        if ($position && $position->unpublish()) {
            $this->dispatch('notify', message: 'Poste dépublié.', type: 'success');
        }
    }

    public function close($id)
    {
        $position = JobPosition::find($id);
        if ($position && $position->close()) {
            $this->dispatch('notify', message: 'Poste clôturé.', type: 'success');
        }
    }

    public function delete($id)
    {
        $position = JobPosition::find($id);
        if ($position) {
            $position->delete();
            $this->dispatch('notify', message: 'Poste supprimé.', type: 'success');
        }
    }

    public function render()
    {
        return view('livewire.hr.recruitment.job-positions-list', [
            'positions' => $this->positions,
            'stats' => $this->stats,
            'departments' => Department::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
