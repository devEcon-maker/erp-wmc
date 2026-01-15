<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Core\Models\Company;
use App\Helpers\NumberToWords;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfController extends Controller
{
    public function download(Invoice $invoice)
    {
        $invoice->load(['contact', 'lines.product', 'creator', 'payments']);

        $company = Company::first();
        $amountInWords = NumberToWords::convert($invoice->total_amount_ttc ?? $invoice->total_amount);

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice', 'company', 'amountInWords'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("facture-{$invoice->reference}.pdf");
    }

    public function stream(Invoice $invoice)
    {
        $invoice->load(['contact', 'lines.product', 'creator', 'payments']);

        $company = Company::first();
        $amountInWords = NumberToWords::convert($invoice->total_amount_ttc ?? $invoice->total_amount);

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice', 'company', 'amountInWords'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("facture-{$invoice->reference}.pdf");
    }
}
