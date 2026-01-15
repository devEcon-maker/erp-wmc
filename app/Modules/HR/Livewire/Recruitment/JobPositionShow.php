<?php

namespace App\Modules\HR\Livewire\Recruitment;

use App\Modules\HR\Models\JobPosition;
use App\Modules\HR\Models\JobApplication;
use Livewire\Component;

class JobPositionShow extends Component
{
    public JobPosition $jobPosition;
    public $applicationStatus = '';
    public $showDeleteModal = false;

    public function mount(JobPosition $jobPosition)
    {
        $this->jobPosition = $jobPosition->load(['department', 'applications']);
    }

    public function getApplicationsProperty()
    {
        return $this->jobPosition->applications()
            ->when($this->applicationStatus, fn($q) => $q->where('status', $this->applicationStatus))
            ->orderBy('applied_at', 'desc')
            ->get();
    }

    public function getStatsProperty(): array
    {
        $applications = $this->jobPosition->applications;

        return [
            'total' => $applications->count(),
            'new' => $applications->where('status', 'new')->count(),
            'reviewing' => $applications->where('status', 'reviewing')->count(),
            'interview' => $applications->where('status', 'interview')->count(),
            'offer' => $applications->where('status', 'offer')->count(),
            'hired' => $applications->where('status', 'hired')->count(),
            'rejected' => $applications->where('status', 'rejected')->count(),
        ];
    }

    public function publish()
    {
        if ($this->jobPosition->publish()) {
            $this->dispatch('notify', message: 'Poste publié.', type: 'success');
            $this->jobPosition->refresh();
        }
    }

    public function unpublish()
    {
        if ($this->jobPosition->unpublish()) {
            $this->dispatch('notify', message: 'Poste dépublié.', type: 'success');
            $this->jobPosition->refresh();
        }
    }

    public function close()
    {
        if ($this->jobPosition->close()) {
            $this->dispatch('notify', message: 'Poste clôturé.', type: 'success');
            $this->jobPosition->refresh();
        }
    }

    public function confirmDelete()
    {
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
    }

    public function deleteJobPosition()
    {
        // Vérifier si le poste a des candidatures
        if ($this->jobPosition->applications->count() > 0) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => "Ce poste ne peut pas être supprimé car il a des candidatures."
            ]);
            $this->showDeleteModal = false;
            return;
        }

        $title = $this->jobPosition->title;
        $this->jobPosition->delete();

        session()->flash('success', "Poste \"{$title}\" supprimé avec succès.");
        $this->js('window.location.href = "' . route('hr.recruitment.positions.index') . '"');
    }

    public function render()
    {
        return view('livewire.hr.recruitment.job-position-show', [
            'applications' => $this->applications,
            'stats' => $this->stats,
        ])->layout('layouts.app');
    }
}
