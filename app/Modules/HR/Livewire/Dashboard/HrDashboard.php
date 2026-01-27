<?php

namespace App\Modules\HR\Livewire\Dashboard;

use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\Attendance;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\HR\Models\HrAlert;
use App\Modules\HR\Models\PayrollPeriod;
use App\Modules\HR\Models\Evaluation;
use App\Modules\HR\Models\JobPosition;
use App\Modules\HR\Models\JobApplication;
use App\Modules\HR\Services\HrAlertService;
use Livewire\Component;
use Carbon\Carbon;

class HrDashboard extends Component
{
    public $employeeStats = [];
    public $attendanceStats = [];
    public $leaveStats = [];
    public $payrollStats = [];
    public $recruitmentStats = [];
    public $alerts = [];

    public function mount()
    {
        $this->loadEmployeeStats();
        $this->loadAttendanceStats();
        $this->loadLeaveStats();
        $this->loadPayrollStats();
        $this->loadRecruitmentStats();
        $this->loadAlerts();
    }

    private function loadEmployeeStats()
    {
        $this->employeeStats = [
            'total' => Employee::count(),
            'active' => Employee::where('status', 'active')->count(),
            'on_leave' => Employee::where('status', 'on_leave')->count(),
            'probation' => Employee::where('probation_completed', false)
                ->whereNotNull('probation_end_date')
                ->count(),
            'new_this_month' => Employee::whereMonth('hire_date', now()->month)
                ->whereYear('hire_date', now()->year)
                ->count(),
            'by_department' => Employee::where('status', 'active')
                ->selectRaw('department_id, COUNT(*) as count')
                ->groupBy('department_id')
                ->with('department:id,name')
                ->get()
                ->map(fn($e) => [
                    'name' => $e->department?->name ?? 'Non assigné',
                    'count' => $e->count,
                ]),
            'by_contract_type' => Employee::where('status', 'active')
                ->selectRaw('contract_type, COUNT(*) as count')
                ->groupBy('contract_type')
                ->get()
                ->mapWithKeys(fn($e) => [$e->contract_type => $e->count]),
        ];
    }

    private function loadAttendanceStats()
    {
        $today = Carbon::today();

        $todayAttendances = Attendance::whereDate('date', $today)->get();

        $this->attendanceStats = [
            'present_today' => $todayAttendances->whereNotNull('check_in')->count(),
            'late_today' => $todayAttendances->where('is_late', true)->count(),
            'absent_today' => Employee::where('status', 'active')->count() - $todayAttendances->whereNotNull('check_in')->count(),
            'average_check_in' => $todayAttendances->whereNotNull('check_in')->avg(function($a) {
                return Carbon::parse($a->check_in)->hour * 60 + Carbon::parse($a->check_in)->minute;
            }),
            'working_now' => $todayAttendances->whereNotNull('check_in')->whereNull('check_out')->count(),
        ];

        // Convertir moyenne en format heure
        if ($this->attendanceStats['average_check_in']) {
            $minutes = (int) $this->attendanceStats['average_check_in'];
            $this->attendanceStats['average_check_in'] = sprintf('%02d:%02d', floor($minutes / 60), $minutes % 60);
        }
    }

    private function loadLeaveStats()
    {
        $this->leaveStats = [
            'pending_requests' => LeaveRequest::where('status', 'pending')->count(),
            'approved_this_month' => LeaveRequest::where('status', 'approved')
                ->whereMonth('created_at', now()->month)
                ->count(),
            'on_leave_today' => LeaveRequest::where('status', 'approved')
                ->whereDate('start_date', '<=', today())
                ->whereDate('end_date', '>=', today())
                ->count(),
            'upcoming_leaves' => LeaveRequest::where('status', 'approved')
                ->whereDate('start_date', '>', today())
                ->whereDate('start_date', '<=', today()->addDays(7))
                ->with('employee:id,first_name,last_name')
                ->limit(5)
                ->get(),
        ];
    }

    private function loadPayrollStats()
    {
        // Trouver la période qui contient la date actuelle
        $currentPeriod = PayrollPeriod::whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        // Dernière période payée
        $lastPeriod = PayrollPeriod::where('status', 'paid')
            ->orderByDesc('end_date')
            ->first();

        $this->payrollStats = [
            'current_period' => $currentPeriod?->name ?? 'Non créée',
            'current_period_status' => $currentPeriod?->status ?? null,
            'last_period_total' => $lastPeriod?->total_net ?? 0,
            'pending_advances' => \App\Modules\HR\Models\SalaryAdvance::where('status', 'pending')->count(),
            'active_loans' => \App\Modules\HR\Models\EmployeeLoan::where('status', 'active')->count(),
            'total_loan_balance' => \App\Modules\HR\Models\EmployeeLoan::where('status', 'active')->sum('remaining_balance'),
        ];
    }

    private function loadRecruitmentStats()
    {
        $this->recruitmentStats = [
            'open_positions' => JobPosition::where('status', 'published')->count(),
            'total_applications' => JobApplication::whereHas('jobPosition', fn($q) => $q->where('status', 'published'))->count(),
            'new_applications' => JobApplication::where('status', 'new')
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->count(),
            'interviews_scheduled' => JobApplication::where('status', 'interview_scheduled')->count(),
            'pipeline' => JobApplication::whereHas('jobPosition', fn($q) => $q->where('status', 'published'))
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->mapWithKeys(fn($a) => [$a->status => $a->count]),
        ];
    }

    private function loadAlerts()
    {
        // Générer les alertes automatiques
        $alertService = app(HrAlertService::class);
        $alertService->generateAlerts();

        // Récupérer les alertes actives
        $this->alerts = HrAlert::where('status', 'pending')
            ->orderBy('priority', 'desc')
            ->orderBy('alert_date')
            ->limit(10)
            ->with('employee:id,first_name,last_name')
            ->get();
    }

    public function acknowledgeAlert($alertId)
    {
        HrAlert::where('id', $alertId)->update(['status' => 'acknowledged']);
        $this->loadAlerts();
        $this->dispatch('notify', type: 'success', message: 'Alerte acquittée.');
    }

    public function resolveAlert($alertId)
    {
        $alert = HrAlert::find($alertId);
        if (!$alert) {
            $this->dispatch('notify', type: 'error', message: 'Alerte introuvable.');
            return;
        }

        $alertService = app(HrAlertService::class);
        $alertService->resolveAlert($alert, auth()->user(), 'Résolu depuis le dashboard');

        $this->loadAlerts();
        $this->dispatch('notify', type: 'success', message: 'Alerte résolue.');
    }

    public function render()
    {
        return view('hr::livewire.dashboard.hr-dashboard')
            ->layout('layouts.app');
    }
}
