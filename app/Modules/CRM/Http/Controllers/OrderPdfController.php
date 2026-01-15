<?php

namespace App\Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderPdfController extends Controller
{
    public function download(Order $order)
    {
        $order->load(['contact', 'lines.product', 'creator']);

        $pdf = Pdf::loadView('pdf.order', compact('order'));

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("commande-{$order->reference}.pdf");
    }

    public function stream(Order $order)
    {
        $order->load(['contact', 'lines.product', 'creator']);

        $pdf = Pdf::loadView('pdf.order', compact('order'));

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("commande-{$order->reference}.pdf");
    }
}
