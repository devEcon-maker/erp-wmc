<?php

namespace App\Modules\HR\Livewire\Attendance;

use App\Modules\HR\Models\Attendance;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\Department;
use App\Modules\HR\Services\AttendanceService;
use Livewire\Component;
use Illuminate\Support\Carbon;

class AttendanceDashboard extends Component
{
    public $selectedDate;
    public $selectedDepartment = '';
    public $todayStats = [];
    public $weeklyStats = [];

    protected AttendanceService $attendanceService;

    public function boot(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function mount()
    {
        $this->selectedDate = now()->toDateString();
        $this->loadStats();
    }

    public function updatedSelectedDate()
    {
        $this->loadStats();
    }

    public function updatedSelectedDepartment()
    {
        $this->loadStats();
    }

    private function loadStats()
    {
        $date = Carbon::parse($this->selectedDate);

        // Stats du jour
        $query = Attendance::whereDate('date', $date);
        if ($this->selectedDepartment) {
            $employeeIds = Employee::where('department_id', $this->selectedDepartment)->pluck('id');
            $query->whereIn('employee_id', $employeeIds);
        }

        $attendances = $query->get();
        $totalEmployees = $this->selectedDepartment
            ? Employee::where('department_id', $this->selectedDepartment)->active()->count()
            : Employee::active()->count();

        $this->todayStats = [
            'total' => $totalEmployees,
            'present' => $attendances->whereIn('status', ['present', 'late', 'remote'])->count(),
            'absent' => $totalEmployees - $attendances->whereIn('status', ['present', 'late', 'remote', 'leave', 'holiday', 'weekend'])->count(),
            'late' => $attendances->where('is_late', true)->count(),
            'on_leave' => $attendances->where('status', 'leave')->count(),
            'remote' => $attendances->where('status', 'remote')->count(),
        ];

        // Stats de la semaine
        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();

        $weekQuery = Attendance::whereBetween('date', [$weekStart, $weekEnd]);
        if ($this->selectedDepartment) {
            $weekQuery->whereIn('employee_id', $employeeIds ?? []);
        }

        $weekAttendances = $weekQuery->get();
        $this->weeklyStats = [
            'total_worked_hours' => round($weekAttendances->sum('worked_hours'), 1),
            'total_overtime' => round($weekAttendances->sum('overtime_hours'), 1),
            'late_count' => $weekAttendances->where('is_late', true)->count(),
            'absent_count' => $weekAttendances->where('status', 'absent')->count(),
        ];
    }

    public function render()
    {
        $date = Carbon::parse($this->selectedDate);

        // Presences du jour avec details
        $query = Attendance::with(['employee.department'])
            ->whereDate('date', $date)
            ->orderBy('check_in');

        if ($this->selectedDepartment) {
            $employeeIds = Employee::where('department_id', $this->selectedDepartment)->pluck('id');
            $query->whereIn('employee_id', $employeeIds);
        }

        $attendances = $query->get();

        // Employes sans pointage
        $checkedInIds = $attendances->pluck('employee_id')->toArray();
        $missingQuery = Employee::active()
            ->whereNotIn('id', $checkedInIds);

        if ($this->selectedDepartment) {
            $missingQuery->where('department_id', $this->selectedDepartment);
        }

        $missingEmployees = $missingQuery->get();

        return view('hr::livewire.attendance.attendance-dashboard', [
            'attendances' => $attendances,
            'missingEmployees' => $missingEmployees,
            'departments' => Department::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
