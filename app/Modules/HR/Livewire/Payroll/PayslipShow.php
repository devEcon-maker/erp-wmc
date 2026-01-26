<?php

namespace App\Modules\HR\Livewire\Payroll;

use App\Modules\HR\Mail\PayslipMail;
use App\Modules\HR\Models\Payslip;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

class PayslipShow extends Component
{
    public Payslip $payslip;

    public function mount(Payslip $payslip)
    {
        $this->payslip = $payslip->load([
            'employee.department',
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
        $employee = $this->payslip->employee;

        if (!$employee->email) {
            $this->dispatch('notify', type: 'error', message: 'L\'employé n\'a pas d\'adresse email configurée.');
            return;
        }

        try {
            Mail::to($employee->email)->send(new PayslipMail($this->payslip));

            $this->dispatch('notify', type: 'success', message: 'Bulletin de paie envoyé à ' . $employee->email);
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Erreur lors de l\'envoi: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('hr::livewire.payroll.payslip-show')
            ->layout('layouts.app');
    }
}
