<?php

namespace App\Modules\HR\Livewire\Timesheets;

use App\Modules\HR\Models\Timesheet;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Services\TimesheetService;
use App\Modules\Productivity\Models\Project;
use Carbon\Carbon;
use Livewire\Component;

class TimesheetDaily extends Component
{
    public $date;
    public $employeeId;

    public $project_id = '';
    public $task_id = '';
    public $hours = '';
    public $description = '';
    public $billable = true;

    protected $rules = [
        'project_id' => 'required',
        'hours' => 'required|numeric|min:0.25|max:24',
        'description' => 'nullable|string|max:500',
    ];

    public function mount()
    {
        $employee = Employee::where('user_id', auth()->id())->first();
        $this->employeeId = $employee?->id;
        $this->date = now()->format('Y-m-d');
    }

    public function previousDay()
    {
        $this->date = Carbon::parse($this->date)->subDay()->format('Y-m-d');
    }

    public function nextDay()
    {
        $next = Carbon::parse($this->date)->addDay();
        if ($next->lte(now())) {
            $this->date = $next->format('Y-m-d');
        }
    }

    public function today()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function getEntriesProperty()
    {
        if (!$this->employeeId) {
            return collect();
        }

        return Timesheet::where('employee_id', $this->employeeId)
            ->whereDate('date', $this->date)
            ->with('project')
            ->orderBy('created_at')
            ->get();
    }

    public function getTotalHoursProperty(): float
    {
        return $this->entries->sum('hours');
    }

    public function addEntry()
    {
        $this->validate();

        if (!$this->employeeId) {
            $this->dispatch('notify', message: 'Vous devez être associé à un employé.', type: 'error');
            return;
        }

        try {
            $service = app(TimesheetService::class);
            $service->upsert($this->employeeId, [
                'project_id' => $this->project_id,
                'task_id' => $this->task_id ?: null,
                'date' => $this->date,
                'hours' => $this->hours,
                'description' => $this->description,
                'billable' => $this->billable,
            ]);

            $this->reset(['project_id', 'task_id', 'hours', 'description']);
            $this->billable = true;

            $this->dispatch('notify', message: 'Entrée ajoutée.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function deleteEntry($id)
    {
        $timesheet = Timesheet::find($id);

        if ($timesheet && $timesheet->employee_id === $this->employeeId && !$timesheet->approved) {
            $timesheet->delete();
            $this->dispatch('notify', message: 'Entrée supprimée.', type: 'success');
        }
    }

    public function render()
    {
        // Charger les projets actifs depuis le module Productivity
        $projects = Project::where('status', 'active')
            ->orWhere('status', 'planning')
            ->orderBy('name')
            ->get();

        return view('livewire.hr.timesheets.timesheet-daily', [
            'entries' => $this->entries,
            'totalHours' => $this->totalHours,
            'projects' => $projects,
        ])->layout('layouts.app');
    }
}
