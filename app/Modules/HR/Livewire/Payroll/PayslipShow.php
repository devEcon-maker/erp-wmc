<?php

namespace App\Modules\HR\Livewire\Payroll;

use App\Modules\HR\Models\Payslip;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

class PayslipShow extends Component
{
    public Payslip $payslip;

    public function mount(Payslip $payslip)
    {
        $this->payslip = $payslip->load([
            'employee.department',
            'employee.jobPosition',
            'payrollPeriod',
            'bonuses.bonusType',
            'deductions.deductionType',
        ]);
    }

    public function downloadPdf()
    {
        $pdf = Pdf::loadView('hr::pdf.payslip', [
            'payslip' => $this->payslip,
        ]);

        $filename = "bulletin_paie_{$this->payslip->employee->employee_id}_{$this->payslip->payrollPeriod->month}_{$this->payslip->payrollPeriod->year}.pdf";

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename
        );
    }

    public function sendByEmail()
    {
        // TODO: Implémenter l'envoi par email
        $this->dispatch('notify', type: 'info', message: 'Fonctionnalité en cours de développement.');
    }

    public function render()
    {
        return view('hr::livewire.payroll.payslip-show')
            ->layout('layouts.app');
    }
}
