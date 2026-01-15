<?php

use Illuminate\Support\Facades\Route;

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
use App\Modules\HR\Livewire\Recruitment\ApplicationShow;
use App\Modules\HR\Livewire\Recruitment\ApplicationForm;

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

// Routes authentifiées
Route::middleware(['auth', 'verified'])->prefix('hr')->name('hr.')->group(function () {

    // === DASHBOARD (requires employees.view) ===
    Route::middleware('permission:employees.view')->group(function () {
        Route::get('/', HrDashboard::class)->name('dashboard');
    });

    // === MON ESPACE (accessible a tous les employes connectes) ===
    Route::get('/my-space', EmployeeSelfService::class)->name('my-space');

    // === EMPLOYEES ===
    Route::prefix('employees')->name('employees.')->group(function () {
        // Liste des employes
        Route::middleware('permission:employees.view')->group(function () {
            Route::get('/', EmployeesList::class)->name('index');
        });
        // Creer un employe - DOIT etre AVANT /{employee}
        Route::middleware('permission:employees.create')->group(function () {
            Route::get('/create', EmployeeForm::class)->name('create');
        });
        // Voir et modifier un employe - APRES /create
        Route::middleware('permission:employees.view')->group(function () {
            Route::get('/{employee}', EmployeeShow::class)->name('show');
        });
        Route::middleware('permission:employees.edit')->group(function () {
            Route::get('/{employee}/edit', EmployeeForm::class)->name('edit');
        });
    });

    Route::middleware('permission:employees.view')->group(function () {
        Route::get('/org-chart', OrgChart::class)->name('org-chart');
    });

    // === LEAVES (Conges) - view ou create ===
    Route::prefix('leaves')->name('leaves.')->group(function () {
        Route::middleware('role_or_permission:leaves.view|leaves.create')->group(function () {
            Route::get('/', LeaveDashboard::class)->name('index');
            Route::get('/balances', LeaveBalances::class)->name('balances');
            Route::get('/requests', LeaveRequestsList::class)->name('requests');
        });
        Route::middleware('permission:leaves.create')->group(function () {
            Route::get('/requests/create', LeaveRequestForm::class)->name('requests.create');
        });
        Route::middleware('permission:leaves.approve')->group(function () {
            Route::get('/approval', LeaveApproval::class)->name('approval');
        });
    });

    // === EXPENSES (Notes de frais) ===
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::middleware('role_or_permission:expenses.view|expenses.create')->group(function () {
            Route::get('/', ExpenseReportsList::class)->name('index');
        });
        // Creer - AVANT /{expenseReport}
        Route::middleware('permission:expenses.create')->group(function () {
            Route::get('/create', ExpenseReportForm::class)->name('create');
        });
        // Voir et modifier - APRES /create
        Route::middleware('role_or_permission:expenses.view|expenses.create')->group(function () {
            Route::get('/{expenseReport}', ExpenseReportShow::class)->name('show');
        });
        Route::middleware('permission:expenses.create')->group(function () {
            Route::get('/{expenseReport}/edit', ExpenseReportForm::class)->name('edit');
        });
    });

    // === TIMESHEETS (Feuilles de temps) ===
    Route::prefix('timesheets')->name('timesheets.')->group(function () {
        Route::middleware('role_or_permission:time.view|time.create')->group(function () {
            Route::get('/', TimesheetWeekly::class)->name('index');
            Route::get('/weekly', TimesheetWeekly::class)->name('weekly');
            Route::get('/daily', TimesheetDaily::class)->name('daily');
        });
    });

    // === ATTENDANCE (Pointage) ===
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::middleware('permission:employees.view')->group(function () {
            Route::get('/', AttendanceDashboard::class)->name('index');
            Route::get('/management', AttendanceManagement::class)->name('management');
        });
        // Mon pointage - accessible a tous
        Route::get('/my', MyAttendance::class)->name('my');
    });

    // === PAYROLL (Paie) ===
    Route::prefix('payroll')->name('payroll.')->group(function () {
        // Periodes de paie - admin/RH only
        Route::middleware('permission:employees.view')->group(function () {
            Route::prefix('periods')->name('periods.')->group(function () {
                Route::get('/', PayrollPeriodsList::class)->name('index');
                Route::get('/{payrollPeriod}', PayrollPeriodShow::class)->name('show');
            });
            Route::get('/payslips/{payslip}', PayslipShow::class)->name('payslips.show');
            Route::get('/advances', SalaryAdvancesList::class)->name('advances.index');
            Route::get('/loans', EmployeeLoansList::class)->name('loans.index');
        });

        // Mes bulletins (self-service) - accessible a tous
        Route::get('/my-payslips', MyPayslips::class)->name('my-payslips');
    });

    // === RECRUITMENT (Recrutement) ===
    Route::prefix('recruitment')->name('recruitment.')->group(function () {
        Route::prefix('positions')->name('positions.')->group(function () {
            Route::middleware('permission:recruitment.view')->group(function () {
                Route::get('/', JobPositionsList::class)->name('index');
            });
            // Creer - AVANT /{jobPosition}
            Route::middleware('permission:recruitment.manage')->group(function () {
                Route::get('/create', JobPositionForm::class)->name('create');
            });
            // Voir et modifier - APRES /create
            Route::middleware('permission:recruitment.view')->group(function () {
                Route::get('/{jobPosition}', JobPositionShow::class)->name('show');
            });
            Route::middleware('permission:recruitment.manage')->group(function () {
                Route::get('/{jobPosition}/edit', JobPositionForm::class)->name('edit');
            });
        });
        Route::middleware('permission:recruitment.view')->group(function () {
            Route::get('/applications', ApplicationsKanban::class)->name('applications.index');
            Route::get('/applications/{application}', ApplicationShow::class)->name('applications.show');
        });
    });

    // === EVALUATIONS ===
    Route::prefix('evaluations')->name('evaluations.')->group(function () {
        Route::middleware('permission:employees.view')->group(function () {
            Route::prefix('periods')->name('periods.')->group(function () {
                Route::get('/', EvaluationPeriodsList::class)->name('index');
                Route::get('/{evaluationPeriod}', EvaluationPeriodShow::class)->name('show');
            });
            Route::get('/form/{evaluation}', EvaluationForm::class)->name('form');
            Route::get('/objectives', ObjectivesList::class)->name('objectives.index');
            Route::get('/development-plans', DevelopmentPlansList::class)->name('development-plans.index');
        });

        // Mes evaluations - accessible a tous
        Route::get('/my-evaluations', MyEvaluations::class)->name('my-evaluations');
    });
});

// === ROUTES PUBLIQUES (Candidatures) ===
Route::prefix('careers')->name('careers.')->group(function () {
    Route::get('/{jobPosition}/apply', ApplicationForm::class)->name('apply');
});
