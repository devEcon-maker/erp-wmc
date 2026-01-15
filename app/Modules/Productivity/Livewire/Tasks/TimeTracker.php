<?php

namespace App\Modules\Productivity\Livewire\Tasks;

use App\Modules\Productivity\Models\Project;
use App\Modules\Productivity\Models\Task;
use App\Modules\Productivity\Models\TimeEntry;
use App\Modules\HR\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TimeTracker extends Component
{
    use WithPagination;

    // Filters
    public $selectedDate;
    public $selectedWeek;
    public $viewMode = 'day'; // day, week
    public $projectId = '';
    public $employeeId = '';

    // Timer
    public $isTracking = false;
    public $trackingProjectId = '';
    public $trackingTaskId = '';
    public $trackingDescription = '';
    public $trackingStart = null;
    public $trackingBillable = true;

    // Manual entry
    public $showEntryModal = false;
    public $entryForm = [
        'project_id' => '',
        'task_id' => '',
        'date' => '',
        'hours' => '',
        'description' => '',
        'billable' => true,
    ];

    protected $queryString = ['viewMode', 'projectId', 'employeeId'];

    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->selectedWeek = now()->startOfWeek()->format('Y-m-d');
        $this->entryForm['date'] = $this->selectedDate;

        // Par défaut, filtrer sur l'employé connecté
        $user = Auth::user();
        if ($user->employee) {
            $this->employeeId = $user->employee->id;
        }
    }

    public function updatedViewMode()
    {
        $this->resetPage();
    }

    public function previousDay()
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->subDay()->format('Y-m-d');
        $this->entryForm['date'] = $this->selectedDate;
    }

    public function nextDay()
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->addDay()->format('Y-m-d');
        $this->entryForm['date'] = $this->selectedDate;
    }

    public function previousWeek()
    {
        $this->selectedWeek = Carbon::parse($this->selectedWeek)->subWeek()->format('Y-m-d');
    }

    public function nextWeek()
    {
        $this->selectedWeek = Carbon::parse($this->selectedWeek)->addWeek()->format('Y-m-d');
    }

    public function goToToday()
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->selectedWeek = now()->startOfWeek()->format('Y-m-d');
        $this->entryForm['date'] = $this->selectedDate;
    }

    // Timer
    public function startTracking()
    {
        if (!$this->trackingProjectId) {
            $this->addError('trackingProjectId', 'Sélectionnez un projet.');
            return;
        }

        $this->isTracking = true;
        $this->trackingStart = now()->toISOString();
        $this->dispatch('notify', message: 'Chronomètre démarré.', type: 'success');
    }

    public function stopTracking()
    {
        if (!$this->isTracking || !$this->trackingStart) {
            return;
        }

        $start = Carbon::parse($this->trackingStart);
        $hours = $start->diffInMinutes(now()) / 60;

        if ($hours < 0.01) {
            $this->dispatch('notify', message: 'Durée trop courte.', type: 'error');
            $this->isTracking = false;
            $this->trackingStart = null;
            return;
        }

        $user = Auth::user();
        $employeeId = $user->employee?->id;

        if (!$employeeId) {
            $this->dispatch('notify', message: 'Profil employé non trouvé.', type: 'error');
            return;
        }

        TimeEntry::create([
            'employee_id' => $employeeId,
            'project_id' => $this->trackingProjectId,
            'task_id' => $this->trackingTaskId ?: null,
            'date' => now()->format('Y-m-d'),
            'hours' => round($hours, 2),
            'description' => $this->trackingDescription ?: null,
            'billable' => $this->trackingBillable,
        ]);

        $this->isTracking = false;
        $this->trackingStart = null;
        $this->trackingProjectId = '';
        $this->trackingTaskId = '';
        $this->trackingDescription = '';
        $this->trackingBillable = true;

        $this->dispatch('notify', message: 'Temps enregistré.', type: 'success');
    }

    // Manual Entry
    public function openEntryModal()
    {
        $this->entryForm = [
            'project_id' => '',
            'task_id' => '',
            'date' => $this->selectedDate,
            'hours' => '',
            'description' => '',
            'billable' => true,
        ];
        $this->showEntryModal = true;
    }

    public function closeEntryModal()
    {
        $this->showEntryModal = false;
    }

    public function saveEntry()
    {
        $this->validate([
            'entryForm.project_id' => 'required|exists:projects,id',
            'entryForm.task_id' => 'nullable|exists:tasks,id',
            'entryForm.date' => 'required|date',
            'entryForm.hours' => 'required|numeric|min:0.1|max:24',
            'entryForm.description' => 'nullable|string',
            'entryForm.billable' => 'boolean',
        ]);

        $user = Auth::user();
        $employeeId = $user->employee?->id;

        if (!$employeeId) {
            $this->addError('entryForm.hours', 'Profil employé non trouvé.');
            return;
        }

        TimeEntry::create([
            'employee_id' => $employeeId,
            'project_id' => $this->entryForm['project_id'],
            'task_id' => $this->entryForm['task_id'] ?: null,
            'date' => $this->entryForm['date'],
            'hours' => $this->entryForm['hours'],
            'description' => $this->entryForm['description'] ?: null,
            'billable' => $this->entryForm['billable'],
        ]);

        $this->closeEntryModal();
        $this->dispatch('notify', message: 'Entrée ajoutée.', type: 'success');
    }

    public function deleteEntry($entryId)
    {
        $entry = TimeEntry::find($entryId);
        $user = Auth::user();

        // Vérifier que l'utilisateur peut supprimer cette entrée
        if ($entry && $entry->employee_id === $user->employee?->id && $entry->approved !== true) {
            $entry->delete();
            $this->dispatch('notify', message: 'Entrée supprimée.', type: 'success');
        }
    }

    // Properties
    public function getEntriesProperty()
    {
        $query = TimeEntry::with(['project', 'task', 'employee']);

        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }

        if ($this->projectId) {
            $query->where('project_id', $this->projectId);
        }

        if ($this->viewMode === 'day') {
            $query->where('date', $this->selectedDate);
        } else {
            $start = Carbon::parse($this->selectedWeek)->startOfWeek();
            $end = $start->copy()->endOfWeek();
            $query->whereBetween('date', [$start, $end]);
        }

        return $query->orderBy('date', 'desc')->orderBy('created_at', 'desc')->get();
    }

    public function getWeekDaysProperty(): array
    {
        $start = Carbon::parse($this->selectedWeek)->startOfWeek();
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $dayEntries = $this->entries->where('date', $date->format('Y-m-d'));

            $days[] = [
                'date' => $date,
                'formatted' => $date->format('Y-m-d'),
                'isToday' => $date->isToday(),
                'isWeekend' => $date->isWeekend(),
                'entries' => $dayEntries,
                'total' => $dayEntries->sum('hours'),
            ];
        }

        return $days;
    }

    public function getTotalHoursProperty(): float
    {
        return $this->entries->sum('hours');
    }

    public function getBillableHoursProperty(): float
    {
        return $this->entries->where('billable', true)->sum('hours');
    }

    public function getProjectsProperty()
    {
        return Project::active()->orderBy('name')->get();
    }

    public function getTasksForProjectProperty()
    {
        $projectId = $this->showEntryModal ? $this->entryForm['project_id'] : $this->trackingProjectId;
        if (!$projectId) {
            return collect([]);
        }

        return Task::where('project_id', $projectId)
            ->whereIn('status', ['todo', 'in_progress', 'review'])
            ->orderBy('title')
            ->get();
    }

    public function getEmployeesProperty()
    {
        return Employee::where('status', 'active')->orderBy('last_name')->get();
    }

    public function render()
    {
        return view('livewire.productivity.tasks.time-tracker', [
            'entries' => $this->entries,
            'weekDays' => $this->weekDays,
            'totalHours' => $this->totalHours,
            'billableHours' => $this->billableHours,
            'projects' => $this->projects,
            'tasksForProject' => $this->tasksForProject,
            'employees' => $this->employees,
        ])->layout('layouts.app');
    }
}
