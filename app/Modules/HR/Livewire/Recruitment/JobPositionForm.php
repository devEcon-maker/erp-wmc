<?php

namespace App\Modules\HR\Livewire\Recruitment;

use App\Modules\HR\Models\JobPosition;
use App\Modules\HR\Models\Department;
use Livewire\Component;

class JobPositionForm extends Component
{
    public ?JobPosition $jobPosition = null;

    public $title = '';
    public $department_id = '';
    public $description = '';
    public $requirements = '';
    public $type = 'full_time';
    public $location = '';
    public $salary_range_min = '';
    public $salary_range_max = '';
    public $closes_at = '';
    public $status = 'draft';

    protected $rules = [
        'title' => 'required|string|max:255',
        'department_id' => 'required|exists:departments,id',
        'description' => 'nullable|string',
        'requirements' => 'nullable|string',
        'type' => 'required|in:full_time,part_time,contract,internship',
        'location' => 'nullable|string|max:255',
        'salary_range_min' => 'nullable|numeric|min:0',
        'salary_range_max' => 'nullable|numeric|min:0|gte:salary_range_min',
        'closes_at' => 'nullable|date|after:today',
        'status' => 'required|in:draft,published',
    ];

    public function mount(JobPosition $jobPosition = null)
    {
        if ($jobPosition && $jobPosition->exists) {
            $this->jobPosition = $jobPosition;
            $this->fill($jobPosition->only([
                'title',
                'department_id',
                'description',
                'requirements',
                'type',
                'location',
                'salary_range_min',
                'salary_range_max',
            ]));
            $this->closes_at = $jobPosition->closes_at?->format('Y-m-d');
            $this->status = $jobPosition->status ?? 'draft';
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'department_id' => $this->department_id,
            'description' => $this->description,
            'requirements' => $this->requirements,
            'type' => $this->type,
            'location' => $this->location ?: null,
            'salary_range_min' => $this->salary_range_min ?: null,
            'salary_range_max' => $this->salary_range_max ?: null,
            'closes_at' => $this->closes_at ?: null,
            'status' => $this->status,
        ];

        if ($this->jobPosition && $this->jobPosition->exists) {
            $this->jobPosition->update($data);
            $message = 'Poste mis à jour.';
        } else {
            $this->jobPosition = JobPosition::create($data);
            $message = 'Poste créé.';
        }

        return redirect()->route('hr.recruitment.positions.show', $this->jobPosition)
            ->with('success', $message);
    }

    public function render()
    {
        return view('livewire.hr.recruitment.job-position-form', [
            'departments' => Department::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
