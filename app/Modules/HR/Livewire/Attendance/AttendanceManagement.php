<?php

namespace App\Modules\HR\Livewire\Attendance;

use App\Modules\HR\Models\Attendance;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\LateArrival;
use App\Modules\HR\Models\Absence;
use App\Modules\HR\Models\PermissionRequest;
use App\Modules\HR\Services\AttendanceService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;

class AttendanceManagement extends Component
{
    use WithPagination;

    public $activeTab = 'attendances';
    public $selectedDate;
    public $selectedDepartment = '';
    public $search = '';
    public $statusFilter = '';

    // Modal de pointage manuel
    public $showManualModal = false;
    public $manualEmployeeId = '';
    public $manualDate = '';
    public $manualCheckIn = '';
    public $manualCheckOut = '';
    public $manualStatus = 'present';
    public $manualNotes = '';

    // Modal de validation retard
    public $showLateModal = false;
    public $selectedLateArrival = null;
    public $lateDecision = '';
    public $deductFromSalary = false;

    protected AttendanceService $attendanceService;

    protected $queryString = ['activeTab', 'selectedDate', 'selectedDepartment', 'statusFilter'];

    public function boot(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function mount()
    {
        $this->selectedDate = now()->toDateString();
        $this->manualDate = now()->toDateString();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    // Pointage manuel
    public function openManualModal()
    {
        $this->reset(['manualEmployeeId', 'manualCheckIn', 'manualCheckOut', 'manualStatus', 'manualNotes']);
        $this->manualDate = $this->selectedDate;
        $this->showManualModal = true;
    }

    public function saveManualAttendance()
    {
        $this->validate([
            'manualEmployeeId' => 'required|exists:employees,id',
            'manualDate' => 'required|date',
            'manualStatus' => 'required',
        ]);

        $employee = Employee::find($this->manualEmployeeId);

        $this->attendanceService->markAttendance(
            $employee,
            Carbon::parse($this->manualDate),
            $this->manualStatus,
            $this->manualCheckIn ?: null,
            $this->manualCheckOut ?: null,
            $this->manualNotes ?: null
        );

        $this->showManualModal = false;
        $this->dispatch('notify', type: 'success', message: 'Pointage enregistre');
    }

    // Gestion des retards
    public function openLateModal($lateId)
    {
        $this->selectedLateArrival = LateArrival::with('employee')->find($lateId);
        $this->reset(['lateDecision', 'deductFromSalary']);
        $this->showLateModal = true;
    }

    public function validateLate()
    {
        if (!$this->selectedLateArrival || !$this->lateDecision) {
            return;
        }

        if ($this->lateDecision === 'excused') {
            $this->selectedLateArrival->excuse(auth()->user());
        } else {
            $this->selectedLateArrival->markUnexcused(auth()->user(), $this->deductFromSalary);
        }

        $this->showLateModal = false;
        $this->dispatch('notify', type: 'success', message: 'Retard traite');
    }

    // Gestion des absences
    public function approveAbsence($absenceId)
    {
        $absence = Absence::find($absenceId);
        if ($absence) {
            $absence->approve(auth()->user());
            $this->dispatch('notify', type: 'success', message: 'Absence approuvee');
        }
    }

    public function rejectAbsence($absenceId, $reason = '')
    {
        $absence = Absence::find($absenceId);
        if ($absence) {
            $absence->reject(auth()->user(), $reason ?: 'Absence non justifiee');
            $this->dispatch('notify', type: 'success', message: 'Absence rejetee');
        }
    }

    // Gestion des permissions
    public function approvePermission($permissionId)
    {
        $permission = PermissionRequest::find($permissionId);
        if ($permission) {
            $permission->approve(auth()->user());
            $this->dispatch('notify', type: 'success', message: 'Permission approuvee');
        }
    }

    public function rejectPermission($permissionId, $reason = '')
    {
        $permission = PermissionRequest::find($permissionId);
        if ($permission) {
            $permission->reject(auth()->user(), $reason ?: 'Permission refusee');
            $this->dispatch('notify', type: 'success', message: 'Permission rejetee');
        }
    }

    public function render()
    {
        $date = Carbon::parse($this->selectedDate);

        // Presences
        $attendancesQuery = Attendance::with(['employee.department'])
            ->whereDate('date', $date);

        if ($this->selectedDepartment) {
            $employeeIds = Employee::where('department_id', $this->selectedDepartment)->pluck('id');
            $attendancesQuery->whereIn('employee_id', $employeeIds);
        }

        if ($this->statusFilter) {
            $attendancesQuery->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $attendancesQuery->whereHas('employee', function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%");
            });
        }

        // Retards en attente
        $lateArrivalsQuery = LateArrival::with(['employee', 'attendance'])
            ->where('status', 'pending')
            ->orderBy('date', 'desc');

        // Absences en attente
        $absencesQuery = Absence::with('employee')
            ->where('status', 'pending')
            ->orderBy('start_date', 'desc');

        // Permissions en attente
        $permissionsQuery = PermissionRequest::with('employee')
            ->where('status', 'pending')
            ->orderBy('date', 'desc');

        return view('hr::livewire.attendance.attendance-management', [
            'attendances' => $attendancesQuery->paginate(20),
            'lateArrivals' => $lateArrivalsQuery->paginate(15),
            'absences' => $absencesQuery->paginate(15),
            'permissions' => $permissionsQuery->paginate(15),
            'departments' => Department::orderBy('name')->get(),
            'employees' => Employee::active()->orderBy('first_name')->get(),
        ])->layout('layouts.app');
    }
}
