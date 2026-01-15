<?php

namespace App\Modules\HR\Livewire\Timesheets;

use App\Modules\HR\Models\Timesheet;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Services\TimesheetService;
use Carbon\Carbon;
use Livewire\Component;

class TimesheetWeekly extends Component
{
    public $weekStart;
    public $entries = [];
    public $employeeId;

    public function mount()
    {
        $employee = Employee::where('user_id', auth()->id())->first();
        $this->employeeId = $employee?->id;

        // Démarrer à la semaine courante (lundi)
        $this->weekStart = now()->startOfWeek()->format('Y-m-d');
        $this->loadEntries();
    }

    public function loadEntries()
    {
        if (!$this->employeeId) {
            return;
        }

        $start = Carbon::parse($this->weekStart);
        $end = $start->copy()->addDays(6);

        $timesheets = Timesheet::where('employee_id', $this->employeeId)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->keyBy(function ($item) {
                return $item->date->format('Y-m-d') . '_' . $item->project_id . '_' . ($item->task_id ?? 0);
            });

        $this->entries = [];

        // Initialiser les entrées pour chaque jour
        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i)->format('Y-m-d');
            $this->entries[$date] = [
                'hours' => 0,
                'timesheets' => $timesheets->filter(fn($t) => $t->date->format('Y-m-d') === $date)->values()->toArray(),
            ];

            foreach ($this->entries[$date]['timesheets'] as &$ts) {
                $this->entries[$date]['hours'] += $ts['hours'];
            }
        }
    }

    public function previousWeek()
    {
        $this->weekStart = Carbon::parse($this->weekStart)->subWeek()->format('Y-m-d');
        $this->loadEntries();
    }

    public function nextWeek()
    {
        $next = Carbon::parse($this->weekStart)->addWeek();
        if ($next->lte(now())) {
            $this->weekStart = $next->format('Y-m-d');
            $this->loadEntries();
        }
    }

    public function currentWeek()
    {
        $this->weekStart = now()->startOfWeek()->format('Y-m-d');
        $this->loadEntries();
    }

    public function saveEntry($date, $projectId, $hours, $description = null, $billable = true)
    {
        if (!$this->employeeId) {
            $this->dispatch('notify', message: 'Vous devez être associé à un employé.', type: 'error');
            return;
        }

        try {
            $service = app(TimesheetService::class);
            $service->upsert($this->employeeId, [
                'project_id' => $projectId,
                'task_id' => null,
                'date' => $date,
                'hours' => $hours,
                'description' => $description,
                'billable' => $billable,
            ]);

            $this->loadEntries();
            $this->dispatch('notify', message: 'Heures enregistrées.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function getWeekDaysProperty(): array
    {
        $start = Carbon::parse($this->weekStart);
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $days[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->locale('fr')->shortDayName,
                'number' => $date->format('d'),
                'isToday' => $date->isToday(),
                'isWeekend' => $date->isWeekend(),
            ];
        }

        return $days;
    }

    public function getTotalWeekHoursProperty(): float
    {
        return collect($this->entries)->sum('hours');
    }

    public function render()
    {
        return view('livewire.hr.timesheets.timesheet-weekly', [
            'weekDays' => $this->weekDays,
            'totalWeekHours' => $this->totalWeekHours,
        ])->layout('layouts.app');
    }
}
