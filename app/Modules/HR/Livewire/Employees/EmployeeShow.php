<?php

namespace App\Modules\HR\Livewire\Employees;

use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\LeaveBalance;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\HR\Models\LeaveType;
use App\Modules\HR\Models\ExpenseReport;
use App\Modules\HR\Models\Timesheet;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class EmployeeShow extends Component
{
    use WithPagination;

    public Employee $employee;
    public $activeTab = 'info';
    public $showDeleteModal = false;

    // Filtres pour les congés
    public $leaveYear;
    public $leaveStatus = '';

    // Filtres pour les notes de frais
    public $expenseStatus = '';

    // Filtres pour les feuilles de temps
    public $timesheetMonth;
    public $timesheetYear;

    public function mount(Employee $employee)
    {
        $this->employee = $employee->load(['department', 'manager', 'directReports', 'user']);
        $this->leaveYear = now()->year;
        $this->timesheetMonth = now()->month;
        $this->timesheetYear = now()->year;
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatedLeaveYear()
    {
        $this->resetPage();
    }

    public function updatedLeaveStatus()
    {
        $this->resetPage();
    }

    public function updatedExpenseStatus()
    {
        $this->resetPage();
    }

    public function updatedTimesheetMonth()
    {
        $this->resetPage();
    }

    public function updatedTimesheetYear()
    {
        $this->resetPage();
    }

    // Données pour l'onglet congés
    public function getLeaveBalancesProperty()
    {
        return LeaveBalance::where('employee_id', $this->employee->id)
            ->where('year', $this->leaveYear)
            ->with('leaveType')
            ->get();
    }

    public function getLeaveRequestsProperty()
    {
        $query = LeaveRequest::where('employee_id', $this->employee->id)
            ->whereYear('start_date', $this->leaveYear)
            ->with(['leaveType', 'approver'])
            ->orderBy('start_date', 'desc');

        if ($this->leaveStatus) {
            $query->where('status', $this->leaveStatus);
        }

        return $query->paginate(10);
    }

    public function getLeaveStatsProperty()
    {
        $requests = LeaveRequest::where('employee_id', $this->employee->id)
            ->whereYear('start_date', $this->leaveYear);

        return [
            'total' => $requests->count(),
            'pending' => (clone $requests)->where('status', 'pending')->count(),
            'approved' => (clone $requests)->where('status', 'approved')->count(),
            'rejected' => (clone $requests)->where('status', 'rejected')->count(),
            'days_taken' => (clone $requests)->where('status', 'approved')->sum('days_count'),
        ];
    }

    // Données pour l'onglet notes de frais
    public function getExpenseReportsProperty()
    {
        $query = ExpenseReport::where('employee_id', $this->employee->id)
            ->with('lines')
            ->orderBy('created_at', 'desc');

        if ($this->expenseStatus) {
            $query->where('status', $this->expenseStatus);
        }

        return $query->paginate(10);
    }

    public function getExpenseStatsProperty()
    {
        $reports = ExpenseReport::where('employee_id', $this->employee->id)
            ->whereYear('created_at', now()->year);

        return [
            'total_reports' => $reports->count(),
            'total_amount' => (clone $reports)->sum('total_amount'),
            'pending_amount' => (clone $reports)->whereIn('status', ['draft', 'submitted'])->sum('total_amount'),
            'approved_amount' => (clone $reports)->where('status', 'approved')->sum('total_amount'),
            'paid_amount' => (clone $reports)->where('status', 'paid')->sum('total_amount'),
        ];
    }

    // Données pour l'onglet feuilles de temps
    public function getTimesheetsProperty()
    {
        $startDate = Carbon::create($this->timesheetYear, $this->timesheetMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return Timesheet::where('employee_id', $this->employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['project', 'task'])
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getTimesheetStatsProperty()
    {
        $startDate = Carbon::create($this->timesheetYear, $this->timesheetMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $timesheets = Timesheet::where('employee_id', $this->employee->id)
            ->whereBetween('date', [$startDate, $endDate]);

        $yearTimesheets = Timesheet::where('employee_id', $this->employee->id)
            ->whereYear('date', $this->timesheetYear);

        return [
            'month_hours' => (clone $timesheets)->sum('hours'),
            'month_billable' => (clone $timesheets)->where('billable', true)->sum('hours'),
            'month_approved' => (clone $timesheets)->where('approved', true)->sum('hours'),
            'year_hours' => (clone $yearTimesheets)->sum('hours'),
            'year_billable' => (clone $yearTimesheets)->where('billable', true)->sum('hours'),
        ];
    }

    public function getTimesheetsByDayProperty()
    {
        $startDate = Carbon::create($this->timesheetYear, $this->timesheetMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return Timesheet::where('employee_id', $this->employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['project', 'task'])
            ->orderBy('date', 'asc')
            ->get()
            ->groupBy(function ($item) {
                return $item->date->format('Y-m-d');
            });
    }

    public function confirmDelete()
    {
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
    }

    public function deleteEmployee()
    {
        $name = $this->employee->full_name;
        $this->employee->delete();

        session()->flash('success', "Employé {$name} supprimé avec succès.");
        $this->js('window.location.href = "' . route('hr.employees.index') . '"');
    }

    public function render()
    {
        return view('hr.employees.employee-show', [
            'leaveBalances' => $this->leaveBalances,
            'leaveRequests' => $this->leaveRequests,
            'leaveStats' => $this->leaveStats,
            'expenseReports' => $this->expenseReports,
            'expenseStats' => $this->expenseStats,
            'timesheets' => $this->timesheets,
            'timesheetStats' => $this->timesheetStats,
            'timesheetsByDay' => $this->timesheetsByDay,
            'leaveTypes' => LeaveType::all(),
        ])->layout('layouts.app');
    }
}
