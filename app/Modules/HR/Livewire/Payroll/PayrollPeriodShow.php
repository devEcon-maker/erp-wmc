<?php

namespace App\Modules\HR\Livewire\Payroll;

use App\Modules\HR\Models\PayrollPeriod;
use App\Modules\HR\Models\Payslip;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Services\PayrollService;
use Livewire\Component;
use Livewire\WithPagination;

class PayrollPeriodShow extends Component
{
    use WithPagination;

    public PayrollPeriod $payrollPeriod;

    public $search = '';
    public $departmentFilter = '';
    public $statusFilter = '';

    // Stats
    public $stats = [];

    public function mount(PayrollPeriod $payrollPeriod)
    {
        $this->payrollPeriod = $payrollPeriod;
        $this->calculateStats();
    }

    private function calculateStats()
    {
        $payslips = $this->payrollPeriod->payslips;

        $this->stats = [
            'total_employees' => $payslips->count(),
            'total_gross' => $payslips->sum('gross_salary'),
            'total_net' => $payslips->sum('net_salary'),
            'total_employer_charges' => $payslips->sum('employer_contributions'),
            'total_employee_deductions' => $payslips->sum('total_deductions'),
            'average_salary' => $payslips->avg('net_salary'),
            'by_status' => $payslips->groupBy('status')->map->count(),
        ];
    }

    public function generatePayslip($employeeId)
    {
        if ($this->payrollPeriod->status !== 'draft') {
            $this->dispatch('notify', type: 'error', message: 'Cette période ne peut plus être modifiée.');
            return;
        }

        $employee = Employee::findOrFail($employeeId);
        $payrollService = app(PayrollService::class);
        $payrollService->generatePayslip($this->payrollPeriod, $employee);

        $this->calculateStats();
        $this->dispatch('notify', type: 'success', message: 'Bulletin généré avec succès.');
    }

    public function regeneratePayslip($payslipId)
    {
        if ($this->payrollPeriod->status !== 'draft') {
            $this->dispatch('notify', type: 'error', message: 'Cette période ne peut plus être modifiée.');
            return;
        }

        $payslip = Payslip::findOrFail($payslipId);
        $payslip->delete();

        $payrollService = app(PayrollService::class);
        $payrollService->generatePayslip($this->payrollPeriod, $payslip->employee);

        $this->calculateStats();
        $this->dispatch('notify', type: 'success', message: 'Bulletin régénéré avec succès.');
    }

    public function validatePayslip($payslipId)
    {
        $payslip = Payslip::findOrFail($payslipId);
        $payslip->update(['status' => 'validated']);

        $this->calculateStats();
        $this->dispatch('notify', type: 'success', message: 'Bulletin validé.');
    }

    public function markPayslipAsPaid($payslipId)
    {
        $payslip = Payslip::findOrFail($payslipId);
        $payslip->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->calculateStats();
        $this->dispatch('notify', type: 'success', message: 'Bulletin marqué comme payé.');
    }

    public function generateAllPayslips()
    {
        if ($this->payrollPeriod->status !== 'draft') {
            $this->dispatch('notify', type: 'error', message: 'Cette période ne peut plus être modifiée.');
            return;
        }

        $payrollService = app(PayrollService::class);
        $count = $payrollService->generatePayslips($this->payrollPeriod);

        $this->calculateStats();
        $this->dispatch('notify', type: 'success', message: "{$count} bulletins générés.");
    }

    public function validatePeriod()
    {
        $payrollService = app(PayrollService::class);
        $payrollService->validatePeriod($this->payrollPeriod);

        $this->payrollPeriod->refresh();
        $this->calculateStats();
        $this->dispatch('notify', type: 'success', message: 'Période validée avec succès.');
    }

    public function markPeriodAsPaid()
    {
        $payrollService = app(PayrollService::class);
        $payrollService->markPeriodAsPaid($this->payrollPeriod);

        $this->payrollPeriod->refresh();
        $this->calculateStats();
        $this->dispatch('notify', type: 'success', message: 'Période marquée comme payée.');
    }

    public function render()
    {
        $payslips = Payslip::query()
            ->where('payroll_period_id', $this->payrollPeriod->id)
            ->with(['employee.department'])
            ->when($this->search, function ($q) {
                $q->whereHas('employee', function ($eq) {
                    $eq->where('first_name', 'like', "%{$this->search}%")
                       ->orWhere('last_name', 'like', "%{$this->search}%")
                       ->orWhere('employee_id', 'like', "%{$this->search}%");
                });
            })
            ->when($this->departmentFilter, function ($q) {
                $q->whereHas('employee', fn($eq) => $eq->where('department_id', $this->departmentFilter));
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $departments = \App\Modules\HR\Models\Department::orderBy('name')->get();

        return view('hr::livewire.payroll.payroll-period-show', [
            'payslips' => $payslips,
            'departments' => $departments,
        ])->layout('layouts.app');
    }
}
