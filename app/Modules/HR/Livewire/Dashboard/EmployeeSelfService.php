<?php

namespace App\Modules\HR\Livewire\Dashboard;

use App\Modules\HR\Models\LeaveRequest;
use App\Modules\HR\Models\LeaveBalance;
use App\Modules\HR\Models\Attendance;
use App\Modules\HR\Models\Payslip;
use App\Modules\HR\Models\EmployeeObjective;
use App\Modules\HR\Models\DevelopmentPlan;
use App\Modules\HR\Models\Evaluation;
use App\Modules\HR\Services\AttendanceService;
use Livewire\Component;
use Carbon\Carbon;

class EmployeeSelfService extends Component
{
    public $activeSection = 'overview';

    public function getEmployeeProperty()
    {
        return auth()->user()->employee;
    }

    public function getTodayAttendanceProperty()
    {
        if (!$this->employee) return null;

        return Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', today())
            ->first();
    }

    public function checkIn()
    {
        if (!$this->employee) return;

        $service = app(AttendanceService::class);
        $attendance = $service->checkIn($this->employee);

        if ($attendance->is_late) {
            $this->dispatch('notify', type: 'warning', message: 'Pointage enregistré. Vous êtes en retard.');
        } else {
            $this->dispatch('notify', type: 'success', message: 'Pointage d\'entrée enregistré.');
        }
    }

    public function checkOut()
    {
        if (!$this->employee || !$this->todayAttendance) return;

        $service = app(AttendanceService::class);
        $service->checkOut($this->employee);

        $this->dispatch('notify', type: 'success', message: 'Pointage de sortie enregistré.');
    }

    public function render()
    {
        $data = [
            'employee' => $this->employee,
            'todayAttendance' => $this->todayAttendance,
        ];

        if ($this->employee) {
            // Soldes de congés
            $data['leaveBalances'] = LeaveBalance::where('employee_id', $this->employee->id)
                ->where('year', now()->year)
                ->with('leaveType')
                ->get();

            // Dernières demandes de congés
            $data['recentLeaveRequests'] = LeaveRequest::where('employee_id', $this->employee->id)
                ->orderByDesc('created_at')
                ->limit(5)
                ->with('leaveType')
                ->get();

            // Statistiques de présence du mois
            $monthStart = now()->startOfMonth();
            $monthEnd = now()->endOfMonth();

            $monthAttendances = Attendance::where('employee_id', $this->employee->id)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->get();

            $data['attendanceStats'] = [
                'present_days' => $monthAttendances->whereNotNull('check_in')->count(),
                'late_days' => $monthAttendances->where('is_late', true)->count(),
                'total_hours' => $monthAttendances->sum('worked_hours'),
                'overtime_hours' => $monthAttendances->sum('overtime_hours'),
            ];

            // Dernier bulletin de paie
            $data['lastPayslip'] = Payslip::where('employee_id', $this->employee->id)
                ->whereIn('status', ['validated', 'paid'])
                ->orderByDesc('created_at')
                ->with('payrollPeriod')
                ->first();

            // Objectifs en cours
            $data['activeObjectives'] = EmployeeObjective::where('employee_id', $this->employee->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->orderBy('due_date')
                ->limit(5)
                ->get();

            // Plans de développement
            $data['developmentPlans'] = DevelopmentPlan::where('employee_id', $this->employee->id)
                ->whereIn('status', ['planned', 'in_progress'])
                ->orderBy('target_date')
                ->limit(3)
                ->get();

            // Évaluations en attente
            $data['pendingEvaluations'] = Evaluation::where('employee_id', $this->employee->id)
                ->whereIn('status', ['pending', 'self_evaluation'])
                ->with('evaluationPeriod')
                ->get();

            // Équipe à évaluer (si manager)
            $data['teamEvaluations'] = Evaluation::where('evaluator_id', auth()->id())
                ->where('status', 'manager_evaluation')
                ->with(['employee', 'evaluationPeriod'])
                ->limit(5)
                ->get();

            // Historique des présences (7 derniers jours)
            $data['recentAttendances'] = Attendance::where('employee_id', $this->employee->id)
                ->whereBetween('date', [now()->subDays(7), now()])
                ->orderByDesc('date')
                ->get();
        }

        return view('hr::livewire.dashboard.employee-self-service', $data)
            ->layout('layouts.app');
    }
}
