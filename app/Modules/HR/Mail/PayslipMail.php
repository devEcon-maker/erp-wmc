<?php

namespace App\Modules\HR\Mail;

use App\Modules\HR\Models\Payslip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayslipMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Payslip $payslip
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bulletin de paie - ' . $this->payslip->payrollPeriod->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'hr::emails.payslip',
            with: [
                'payslip' => $this->payslip,
                'employee' => $this->payslip->employee,
                'period' => $this->payslip->payrollPeriod,
            ],
        );
    }

    public function attachments(): array
    {
        $pdf = Pdf::loadView('hr::pdf.payslip', [
            'payslip' => $this->payslip,
        ]);

        $filename = "bulletin_paie_{$this->payslip->employee->employee_number}_{$this->payslip->payrollPeriod->month}_{$this->payslip->payrollPeriod->year}.pdf";

        return [
            Attachment::fromData(fn () => $pdf->output(), $filename)
                ->withMime('application/pdf'),
        ];
    }
}
