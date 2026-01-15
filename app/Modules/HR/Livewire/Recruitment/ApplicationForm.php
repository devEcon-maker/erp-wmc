<?php

namespace App\Modules\HR\Livewire\Recruitment;

use App\Modules\HR\Models\JobApplication;
use App\Modules\HR\Models\JobPosition;
use Livewire\Component;
use Livewire\WithFileUploads;

class ApplicationForm extends Component
{
    use WithFileUploads;

    public JobPosition $jobPosition;

    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $phone = '';
    public $cover_letter = '';
    public $resume;

    public $submitted = false;

    protected $rules = [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:20',
        'cover_letter' => 'nullable|string|max:5000',
        'resume' => 'required|file|mimes:pdf,doc,docx|max:5120', // 5MB max
    ];

    public function mount(JobPosition $jobPosition)
    {
        // Vérifier que le poste est publié et ouvert
        if ($jobPosition->status !== 'published') {
            abort(404);
        }

        if ($jobPosition->closes_at && $jobPosition->closes_at < now()) {
            abort(404, 'Ce poste n\'accepte plus de candidatures.');
        }

        $this->jobPosition = $jobPosition;
    }

    public function submit()
    {
        $this->validate();

        // Stocker le CV
        $resumePath = $this->resume->store('applications', 'private');

        // Créer la candidature
        JobApplication::create([
            'job_position_id' => $this->jobPosition->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'cover_letter' => $this->cover_letter,
            'resume_path' => $resumePath,
            'status' => 'new',
            'applied_at' => now(),
        ]);

        // TODO: Envoyer email de confirmation au candidat
        // TODO: Notifier le recruteur

        $this->submitted = true;
    }

    public function render()
    {
        // Vue publique sans layout authentifié
        return view('livewire.hr.recruitment.application-form')
            ->layout('layouts.guest');
    }
}
