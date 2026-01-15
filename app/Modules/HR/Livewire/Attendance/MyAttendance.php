<?php

namespace App\Modules\HR\Livewire\Attendance;

use App\Modules\HR\Models\Attendance;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Services\AttendanceService;
use Livewire\Component;
use Illuminate\Support\Carbon;

class MyAttendance extends Component
{
    public ?Employee $employee = null;
    public $todayAttendance = null;
    public $selectedMonth;
    public $selectedYear;

    protected AttendanceService $attendanceService;

    public function boot(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function mount()
    {
        $this->employee = Employee::where('user_id', auth()->id())->first();
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
        $this->loadTodayAttendance();
    }

    private function loadTodayAttendance()
    {
        if ($this->employee) {
            $this->todayAttendance = Attendance::where('employee_id', $this->employee->id)
                ->where('date', now()->toDateString())
                ->first();
        }
    }

    public function checkIn()
    {
        if (!$this->employee) {
            $this->dispatch('notify', type: 'error', message: 'Profil employe non trouve');
            return;
        }

        try {
            $this->attendanceService->checkIn($this->employee);
            $this->loadTodayAttendance();
            $this->dispatch('notify', type: 'success', message: 'Pointage d\'entree enregistre');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function checkOut()
    {
        if (!$this->employee) {
            $this->dispatch('notify', type: 'error', message: 'Profil employe non trouve');
            return;
        }

        try {
            $this->attendanceService->checkOut($this->employee);
            $this->loadTodayAttendance();
            $this->dispatch('notify', type: 'success', message: 'Pointage de sortie enregistre');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function render()
    {
        $monthAttendances = collect();
        $monthStats = [];

        if ($this->employee) {
            $startDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1);
            $endDate = $startDate->copy()->endOfMonth();

            $monthAttendances = Attendance::where('employee_id', $this->employee->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'desc')
                ->get();

            $monthStats = $this->attendanceService->getEmployeeReport(
                $this->employee,
                $startDate,
                $endDate
            );
        }

        return view('hr::livewire.attendance.my-attendance', [
            'monthAttendances' => $monthAttendances,
            'monthStats' => $monthStats,
        ])->layout('layouts.app');
    }
}
