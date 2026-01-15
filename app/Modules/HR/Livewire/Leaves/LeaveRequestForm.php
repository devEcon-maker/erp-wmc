<?php

namespace App\Modules\HR\Livewire\Leaves;

use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\LeaveType;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\HR\Services\LeaveService;
use Livewire\Component;
use Livewire\WithFileUploads;

class LeaveRequestForm extends Component
{
    use WithFileUploads;

    public $leave_type_id;
    public $start_date;
    public $end_date;
    public $reason;
    public $days_count = 0;
    public $justification;

    // Pour savoir si le type selectionne requiert un justificatif
    public $requiresJustification = false;

    public function mount()
    {
        $firstType = LeaveType::first();
        $this->leave_type_id = $firstType?->id;
        $this->updateJustificationRequirement();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['start_date', 'end_date'])) {
            $this->calculateDuration();
        }

        if ($propertyName === 'leave_type_id') {
            $this->updateJustificationRequirement();
        }
    }

    public function updateJustificationRequirement()
    {
        if ($this->leave_type_id) {
            $leaveType = LeaveType::find($this->leave_type_id);
            $this->requiresJustification = $leaveType?->requires_justification ?? false;
        } else {
            $this->requiresJustification = false;
        }

        // Reset le fichier si le type ne requiert plus de justificatif
        if (!$this->requiresJustification) {
            $this->justification = null;
        }
    }

    public function calculateDuration()
    {
        if ($this->start_date && $this->end_date) {
            $service = new LeaveService();
            $this->days_count = $service->calculateDaysCount($this->start_date, $this->end_date);
        } else {
            $this->days_count = 0;
        }
    }

    protected function rules()
    {
        $rules = [
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
        ];

        // Si le type de conge requiert un justificatif, le rendre obligatoire
        if ($this->requiresJustification) {
            $rules['justification'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'; // 5MB max
        } else {
            $rules['justification'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
        }

        return $rules;
    }

    protected $messages = [
        'justification.required' => 'Un justificatif medical est obligatoire pour ce type de conge.',
        'justification.mimes' => 'Le justificatif doit etre un fichier PDF ou une image (JPG, PNG).',
        'justification.max' => 'Le justificatif ne doit pas depasser 5 Mo.',
    ];

    public function save()
    {
        $this->validate();

        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            $this->dispatch('notify', type: 'error', message: 'Aucun profil employe trouve.');
            return;
        }

        try {
            $service = new LeaveService();
            $leaveRequest = $service->submitRequest($employee, [
                'leave_type_id' => $this->leave_type_id,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'reason' => $this->reason,
            ]);

            // Upload du justificatif si present
            if ($this->justification) {
                $this->uploadJustification($leaveRequest);
            }

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Demande de congé soumise avec succès.'
            ]);

            session()->flash('success', 'Demande de congé soumise avec succès.');
            return redirect()->route('hr.leaves.index');
        } catch (\Exception $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
            $this->addError('start_date', $e->getMessage());
        }
    }

    protected function uploadJustification(LeaveRequest $leaveRequest)
    {
        $originalName = $this->justification->getClientOriginalName();
        $extension = $this->justification->getClientOriginalExtension();
        $size = $this->justification->getSize();

        // Stocker le fichier
        $path = $this->justification->store('leave-justifications/' . date('Y/m'), 'public');

        $leaveRequest->update([
            'justification_path' => $path,
            'justification_name' => $originalName,
            'justification_type' => $extension,
            'justification_size' => $size,
            'justification_uploaded_at' => now(),
        ]);
    }

    public function render()
    {
        return view('hr.leaves.leave-request-form', [
            'leaveTypes' => LeaveType::all()
        ])->layout('layouts.app');
    }
}
