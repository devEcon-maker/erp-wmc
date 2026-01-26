<?php

namespace App\Modules\HR\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

// Employees
use App\Modules\HR\Livewire\Employees\EmployeesList;
use App\Modules\HR\Livewire\Employees\EmployeeForm;
use App\Modules\HR\Livewire\Employees\EmployeeShow;
use App\Modules\HR\Livewire\Employees\OrgChart;

// Leaves
use App\Modules\HR\Livewire\Leaves\LeaveDashboard;
use App\Modules\HR\Livewire\Leaves\LeaveBalances;
use App\Modules\HR\Livewire\Leaves\LeaveRequestForm;
use App\Modules\HR\Livewire\Leaves\LeaveRequestsList;
use App\Modules\HR\Livewire\Leaves\LeaveApproval;

// Expenses
use App\Modules\HR\Livewire\Expenses\ExpenseReportsList;
use App\Modules\HR\Livewire\Expenses\ExpenseReportForm;
use App\Modules\HR\Livewire\Expenses\ExpenseReportShow;

// Timesheets
use App\Modules\HR\Livewire\Timesheets\TimesheetWeekly;
use App\Modules\HR\Livewire\Timesheets\TimesheetDaily;

// Recruitment
use App\Modules\HR\Livewire\Recruitment\JobPositionsList;
use App\Modules\HR\Livewire\Recruitment\JobPositionForm;
use App\Modules\HR\Livewire\Recruitment\JobPositionShow;
use App\Modules\HR\Livewire\Recruitment\ApplicationsKanban;
use App\Modules\HR\Livewire\Recruitment\ApplicationForm;
use App\Modules\HR\Livewire\Recruitment\ApplicationShow;

// Attendance
use App\Modules\HR\Livewire\Attendance\AttendanceDashboard;
use App\Modules\HR\Livewire\Attendance\AttendanceManagement;
use App\Modules\HR\Livewire\Attendance\MyAttendance;

// Payroll
use App\Modules\HR\Livewire\Payroll\PayrollPeriodsList;
use App\Modules\HR\Livewire\Payroll\PayrollPeriodShow;
use App\Modules\HR\Livewire\Payroll\PayslipShow;
use App\Modules\HR\Livewire\Payroll\MyPayslips;
use App\Modules\HR\Livewire\Payroll\SalaryAdvancesList;
use App\Modules\HR\Livewire\Payroll\EmployeeLoansList;

// Evaluations
use App\Modules\HR\Livewire\Evaluations\EvaluationPeriodsList;
use App\Modules\HR\Livewire\Evaluations\EvaluationPeriodShow;
use App\Modules\HR\Livewire\Evaluations\EvaluationForm;
use App\Modules\HR\Livewire\Evaluations\MyEvaluations;
use App\Modules\HR\Livewire\Evaluations\ObjectivesList;
use App\Modules\HR\Livewire\Evaluations\DevelopmentPlansList;

// Dashboard
use App\Modules\HR\Livewire\Dashboard\HrDashboard;
use App\Modules\HR\Livewire\Dashboard\EmployeeSelfService;

// Tasks
use App\Modules\HR\Livewire\Tasks\TasksList;
use App\Modules\HR\Livewire\Tasks\TaskForm;
use App\Modules\HR\Livewire\Tasks\TaskShow;
use App\Modules\HR\Livewire\Tasks\TaskBoard;
use App\Modules\HR\Livewire\Tasks\MyTasks;

// Services
use App\Modules\HR\Services\PayrollService;
use App\Modules\HR\Services\AttendanceService;
use App\Modules\HR\Services\HrAlertService;
use App\Modules\HR\Services\EvaluationService;

class HRServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'hr');

        // Load routes
        Route::middleware('web')
            ->group(__DIR__ . '/../routes.php');

        // Register Livewire components - Employees
        Livewire::component('hr.employees.employees-list', EmployeesList::class);
        Livewire::component('hr.employees.employee-form', EmployeeForm::class);
        Livewire::component('hr.employees.employee-show', EmployeeShow::class);
        Livewire::component('hr.employees.org-chart', OrgChart::class);

        // Register Livewire components - Leaves
        Livewire::component('hr.leaves.leave-dashboard', LeaveDashboard::class);
        Livewire::component('hr.leaves.leave-balances', LeaveBalances::class);
        Livewire::component('hr.leaves.leave-request-form', LeaveRequestForm::class);
        Livewire::component('hr.leaves.leave-requests-list', LeaveRequestsList::class);
        Livewire::component('hr.leaves.leave-approval', LeaveApproval::class);

        // Register Livewire components - Expenses
        Livewire::component('hr.expenses.expense-reports-list', ExpenseReportsList::class);
        Livewire::component('hr.expenses.expense-report-form', ExpenseReportForm::class);
        Livewire::component('hr.expenses.expense-report-show', ExpenseReportShow::class);

        // Register Livewire components - Timesheets
        Livewire::component('hr.timesheets.timesheet-weekly', TimesheetWeekly::class);
        Livewire::component('hr.timesheets.timesheet-daily', TimesheetDaily::class);

        // Register Livewire components - Recruitment
        Livewire::component('hr.recruitment.job-positions-list', JobPositionsList::class);
        Livewire::component('hr.recruitment.job-position-form', JobPositionForm::class);
        Livewire::component('hr.recruitment.job-position-show', JobPositionShow::class);
        Livewire::component('hr.recruitment.applications-kanban', ApplicationsKanban::class);
        Livewire::component('hr.recruitment.application-form', ApplicationForm::class);
        Livewire::component('hr.recruitment.application-show', ApplicationShow::class);

        // Register Livewire components - Attendance
        Livewire::component('hr.attendance.attendance-dashboard', AttendanceDashboard::class);
        Livewire::component('hr.attendance.attendance-management', AttendanceManagement::class);
        Livewire::component('hr.attendance.my-attendance', MyAttendance::class);

        // Register Livewire components - Payroll
        Livewire::component('hr.payroll.payroll-periods-list', PayrollPeriodsList::class);
        Livewire::component('hr.payroll.payroll-period-show', PayrollPeriodShow::class);
        Livewire::component('hr.payroll.payslip-show', PayslipShow::class);
        Livewire::component('hr.payroll.my-payslips', MyPayslips::class);
        Livewire::component('hr.payroll.salary-advances-list', SalaryAdvancesList::class);
        Livewire::component('hr.payroll.employee-loans-list', EmployeeLoansList::class);

        // Register Livewire components - Evaluations
        Livewire::component('hr.evaluations.evaluation-periods-list', EvaluationPeriodsList::class);
        Livewire::component('hr.evaluations.evaluation-period-show', EvaluationPeriodShow::class);
        Livewire::component('hr.evaluations.evaluation-form', EvaluationForm::class);
        Livewire::component('hr.evaluations.my-evaluations', MyEvaluations::class);
        Livewire::component('hr.evaluations.objectives-list', ObjectivesList::class);
        Livewire::component('hr.evaluations.development-plans-list', DevelopmentPlansList::class);

        // Register Livewire components - Dashboard
        Livewire::component('hr.dashboard.hr-dashboard', HrDashboard::class);
        Livewire::component('hr.dashboard.employee-self-service', EmployeeSelfService::class);

        // Register Livewire components - Tasks
        Livewire::component('hr.tasks.tasks-list', TasksList::class);
        Livewire::component('hr.tasks.task-form', TaskForm::class);
        Livewire::component('hr.tasks.task-show', TaskShow::class);
        Livewire::component('hr.tasks.task-board', TaskBoard::class);
        Livewire::component('hr.tasks.my-tasks', MyTasks::class);
    }

    public function register(): void
    {
        $this->app->singleton(PayrollService::class, PayrollService::class);
        $this->app->singleton(AttendanceService::class, AttendanceService::class);
        $this->app->singleton(HrAlertService::class, HrAlertService::class);
        $this->app->singleton(EvaluationService::class, EvaluationService::class);
    }
}
