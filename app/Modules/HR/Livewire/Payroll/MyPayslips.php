<?php

namespace App\Modules\HR\Livewire\Payroll;

use App\Modules\HR\Models\Payslip;
use Livewire\Component;
use Livewire\WithPagination;

class MyPayslips extends Component
{
    use WithPagination;

    public $yearFilter = '';

    public function mount()
    {
        $this->yearFilter = date('Y');
    }

    public function getEmployeeProperty()
    {
        return auth()->user()->employee;
    }

    public function render()
    {
        $payslips = collect();
        $years = collect();
        $stats = [
            'total_net' => 0,
            'total_gross' => 0,
            'count' => 0,
        ];

        if ($this->employee) {
            $payslips = Payslip::query()
                ->where('employee_id', $this->employee->id)
                ->with('payrollPeriod')
                ->when($this->yearFilter, function ($q) {
                    $q->whereHas('payrollPeriod', fn($pq) => $pq->whereYear('start_date', $this->yearFilter));
                })
                ->whereIn('status', ['validated', 'paid'])
                ->orderByDesc('created_at')
                ->paginate(12);

            $years = Payslip::query()
                ->where('employee_id', $this->employee->id)
                ->join('payroll_periods', 'payslips.payroll_period_id', '=', 'payroll_periods.id')
                ->selectRaw('YEAR(payroll_periods.start_date) as year')
                ->distinct()
                ->pluck('year')
                ->sortDesc();

            $yearPayslips = Payslip::query()
                ->where('employee_id', $this->employee->id)
                ->whereHas('payrollPeriod', fn($pq) => $pq->whereYear('start_date', $this->yearFilter ?: date('Y')))
                ->whereIn('status', ['validated', 'paid'])
                ->get();

            $stats = [
                'total_net' => $yearPayslips->sum('net_salary'),
                'total_gross' => $yearPayslips->sum('gross_salary'),
                'count' => $yearPayslips->count(),
            ];
        }

        return view('hr::livewire.payroll.my-payslips', [
            'payslips' => $payslips,
            'years' => $years,
            'stats' => $stats,
        ])->layout('layouts.app');
    }
}
