<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\PurchaseOrder;
use App\Modules\Inventory\Models\PurchaseOrderLine;
use App\Modules\Inventory\Models\StockLevel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PurchaseOrderService
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function calculateTotals(PurchaseOrder $purchaseOrder)
    {
        $totalHt = 0;
        $totalTva = 0;

        foreach ($purchaseOrder->lines as $line) {
            $lineTotalHt = $line->quantity * $line->unit_price;
            $lineTotalTva = $lineTotalHt * ($line->tax_rate / 100);

            $totalHt += $lineTotalHt;
            $totalTva += $lineTotalTva;
        }

        $purchaseOrder->update([
            'total_ht' => $totalHt,
            'total_tva' => $totalTva,
            'total_ttc' => $totalHt + $totalTva,
        ]);

        return $purchaseOrder;
    }

    public function send(PurchaseOrder $purchaseOrder)
    {
        // Require status draft
        if ($purchaseOrder->status !== 'draft') {
            return false;
        }

        $pdf = $this->generatePDF($purchaseOrder);

        // TODO: Send email
        // Mail::to($purchaseOrder->supplier->email)->send(new PurchaseOrderSent($purchaseOrder, $pdf));

        $purchaseOrder->update(['status' => 'sent']);
        return true;
    }

    public function receivePartial(PurchaseOrder $purchaseOrder, array $receivedQuantities)
    {
        // receivedQuantities: [line_id => quantity_received_now]

        return DB::transaction(function () use ($purchaseOrder, $receivedQuantities) {
            $allReceived = true;
            $defaultWarehouse = \App\Modules\Inventory\Models\Warehouse::where('is_default', true)->first();
            $warehouseId = $defaultWarehouse ? $defaultWarehouse->id : 1; // Fallback

            foreach ($purchaseOrder->lines as $line) {
                if (isset($receivedQuantities[$line->id]) && $receivedQuantities[$line->id] > 0) {
                    $qty = $receivedQuantities[$line->id];

                    // Update line received qty
                    $line->increment('received_qty', $qty);

                    // Add to stock
                    $this->stockService->addStock(
                        $line->product_id,
                        $warehouseId,
                        $qty,
                        'Purchase Order Receipt ' . $purchaseOrder->reference,
                        $purchaseOrder
                    );
                }

                if ($line->fresh()->received_qty < $line->quantity) {
                    $allReceived = false;
                }
            }

            $purchaseOrder->status = $allReceived ? 'received' : 'partial';
            $purchaseOrder->save();

            return $purchaseOrder;
        });
    }

    public function receiveAll(PurchaseOrder $purchaseOrder)
    {
        return DB::transaction(function () use ($purchaseOrder) {
            $receivedQuantities = [];

            foreach ($purchaseOrder->lines as $line) {
                $remaining = $line->quantity - $line->received_qty;
                if ($remaining > 0) {
                    $receivedQuantities[$line->id] = $remaining;
                }
            }

            return $this->receivePartial($purchaseOrder, $receivedQuantities);
        });
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, ['received', 'partial'])) {
            // Cannot simple cancel if stock was received. Needs return logic.
            // For now assumption: only cancel if nothing received
            return false;
        }

        $purchaseOrder->update(['status' => 'cancelled']);
        return true;
    }

    public function generatePDF(PurchaseOrder $purchaseOrder)
    {
        // $pdf = Pdf::loadView('pdf.purchase_order', ['po' => $purchaseOrder]);
        // return $pdf->output();
        return "PDF Content"; // Placeholder
    }

    public function createFromStockAlerts()
    {
        // Logic to find products below min_stock_alert and create POs grouped by supplier
        // To be implemented
    }
}
