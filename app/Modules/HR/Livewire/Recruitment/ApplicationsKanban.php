<?php

namespace App\Modules\HR\Livewire\Recruitment;

use App\Modules\HR\Models\JobApplication;
use App\Modules\HR\Models\JobPosition;
use Livewire\Component;

class ApplicationsKanban extends Component
{
    public ?int $jobPositionId = null;

    public $statuses = [
        'new' => ['label' => 'Nouvelles', 'color' => 'blue'],
        'reviewing' => ['label' => 'En revue', 'color' => 'yellow'],
        'interview' => ['label' => 'Entretien', 'color' => 'purple'],
        'offer' => ['label' => 'Offre', 'color' => 'green'],
        'hired' => ['label' => 'Embauchés', 'color' => 'emerald'],
        'rejected' => ['label' => 'Rejetés', 'color' => 'red'],
    ];

    public function mount(?int $jobPositionId = null)
    {
        $this->jobPositionId = $jobPositionId;
    }

    public function getApplicationsByStatusProperty(): array
    {
        $query = JobApplication::with('jobPosition');

        if ($this->jobPositionId) {
            $query->where('job_position_id', $this->jobPositionId);
        }

        $applications = $query->orderBy('applied_at', 'desc')->get();

        $byStatus = [];
        foreach ($this->statuses as $status => $config) {
            $byStatus[$status] = $applications->where('status', $status)->values();
        }

        return $byStatus;
    }

    public function updateStatus($applicationId, $newStatus)
    {
        $application = JobApplication::find($applicationId);

        if ($application && array_key_exists($newStatus, $this->statuses)) {
            $application->update(['status' => $newStatus]);
            $this->dispatch('notify', message: 'Statut mis à jour.', type: 'success');
        }
    }

    public function render()
    {
        return view('livewire.hr.recruitment.applications-kanban', [
            'applicationsByStatus' => $this->applicationsByStatus,
            'positions' => JobPosition::orderBy('title')->get(),
        ])->layout('layouts.app');
    }
}
