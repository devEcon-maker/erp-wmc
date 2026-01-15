<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfController extends Controller
{
    public function download(Invoice $invoice)
    {
        $invoice->load(['contact', 'lines.product', 'creator', 'payments']);

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("facture-{$invoice->reference}.pdf");
    }

    public function stream(Invoice $invoice)
    {
        $invoice->load(['contact', 'lines.product', 'creator', 'payments']);

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("facture-{$invoice->reference}.pdf");
    }
}
