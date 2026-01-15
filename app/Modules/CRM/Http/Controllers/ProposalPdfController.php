<?php

namespace App\Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Models\Proposal;
use Barryvdh\DomPDF\Facade\Pdf;

class ProposalPdfController extends Controller
{
    public function download(Proposal $proposal)
    {
        $proposal->load(['contact', 'lines.product', 'creator']);

        $pdf = Pdf::loadView('pdf.proposal', compact('proposal'));

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("devis-{$proposal->reference}.pdf");
    }

    public function stream(Proposal $proposal)
    {
        $proposal->load(['contact', 'lines.product', 'creator']);

        $pdf = Pdf::loadView('pdf.proposal', compact('proposal'));

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("devis-{$proposal->reference}.pdf");
    }
}
