<?php

namespace App\Modules\HR\Livewire\Recruitment;

use App\Modules\HR\Models\JobApplication;
use App\Modules\HR\Models\Employee;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class ApplicationShow extends Component
{
    public JobApplication $application;
    public $notes = '';
    public $rating = null;
    public $showDeleteModal = false;

    public function mount(JobApplication $application)
    {
        $this->application = $application->load('jobPosition.department');
        $this->notes = $application->notes ?? '';
        $this->rating = $application->rating;
    }

    public function saveNotes()
    {
        $this->application->update([
            'notes' => $this->notes,
            'rating' => $this->rating,
        ]);

        $this->dispatch('notify', message: 'Notes enregistrées.', type: 'success');
    }

    public function setRating($rating)
    {
        $this->rating = $rating;
        $this->application->update(['rating' => $rating]);
        $this->dispatch('notify', message: 'Note mise à jour.', type: 'success');
    }

    public function markAsReviewing()
    {
        $this->application->markAsReviewing();
        $this->dispatch('notify', message: 'Candidature passée en revue.', type: 'success');
    }

    public function scheduleInterview()
    {
        $this->application->scheduleInterview();
        $this->dispatch('notify', message: 'Entretien planifié.', type: 'success');
    }

    public function makeOffer()
    {
        $this->application->makeOffer();
        $this->dispatch('notify', message: 'Offre envoyée.', type: 'success');
    }

    public function hire()
    {
        $this->application->hire();
        $this->dispatch('notify', message: 'Candidat embauché !', type: 'success');
    }

    public function reject()
    {
        $this->application->reject();
        $this->dispatch('notify', message: 'Candidature rejetée.', type: 'success');
    }

    public function createEmployee()
    {
        if ($this->application->status !== 'hired') {
            $this->dispatch('notify', message: 'Le candidat doit d\'abord être embauché.', type: 'error');
            return;
        }

        // Vérifier si un employé existe déjà avec cet email
        if (Employee::where('email', $this->application->email)->exists()) {
            $this->dispatch('notify', message: 'Un employé avec cet email existe déjà.', type: 'error');
            return;
        }

        $employee = $this->application->createEmployee();

        session()->flash('success', 'Fiche employé créée avec succès.');
        $this->js('window.location.href = "' . route('hr.employees.show', $employee->id) . '"');
    }

    public function downloadResume()
    {
        if ($this->application->resume_path && Storage::disk('private')->exists($this->application->resume_path)) {
            return Storage::disk('private')->download($this->application->resume_path);
        }

        $this->dispatch('notify', message: 'CV non disponible.', type: 'error');
    }

    public function confirmDelete()
    {
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
    }

    public function deleteApplication()
    {
        $name = $this->application->full_name;
        $jobPositionId = $this->application->job_position_id;

        // Supprimer le CV si présent
        if ($this->application->resume_path && Storage::disk('private')->exists($this->application->resume_path)) {
            Storage::disk('private')->delete($this->application->resume_path);
        }

        $this->application->delete();

        session()->flash('success', "Candidature de {$name} supprimée avec succès.");
        $this->js('window.location.href = "' . route('hr.recruitment.positions.show', $jobPositionId) . '"');
    }

    public function render()
    {
        return view('livewire.hr.recruitment.application-show')->layout('layouts.app');
    }
}
