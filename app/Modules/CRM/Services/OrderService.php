<?php

namespace App\Modules\CRM\Services;

use App\Modules\CRM\Models\Order;
use App\Modules\CRM\Models\Proposal;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected $stockService;

    public function __construct(\App\Modules\Inventory\Services\StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    // ... existing generateReference, calculateTotals, createFromProposal ...
    public function generateReference(): string
    {
        // Format: CMD-YYYYMM-XXXX (e.g., CMD-202401-0001)
        $prefix = 'CMD-' . date('Ym') . '-';
        $lastOrder = Order::where('reference', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = intval(substr($lastOrder->reference, strlen($prefix)));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function calculateTotals(Order $order): void
    {
        $lines = $order->lines;

        $totalAmount = 0;
        $taxAmount = 0;
        $discountAmount = 0;

        foreach ($lines as $line) {
            $lineTotal = $line->quantity * $line->unit_price;
            $lineDiscount = $lineTotal * ($line->discount_rate / 100);
            $lineTotalAfterDiscount = $lineTotal - $lineDiscount;

            $lineTax = $lineTotalAfterDiscount * ($line->tax_rate / 100);

            $totalAmount += $lineTotal;
            $discountAmount += $lineDiscount;
            $taxAmount += $lineTax;

            // Update line total just in case
            $line->total_amount = $lineTotalAfterDiscount;
            $line->saveQuietly();
        }

        $order->total_amount = $totalAmount;
        $order->discount_amount = $discountAmount;
        $order->tax_amount = $taxAmount;
        $order->total_amount_ttc = ($totalAmount - $discountAmount) + $taxAmount;

        $order->save();
    }

    public function createFromProposal(Proposal $proposal): Order
    {
        return DB::transaction(function () use ($proposal) {
            $order = new Order();
            $order->contact_id = $proposal->contact_id;
            $order->proposal_id = $proposal->id;
            $order->reference = $this->generateReference();
            $order->status = 'draft';
            $order->order_date = now();
            $order->created_by = auth()->id();
            $order->notes = $proposal->notes;
            $order->save();

            foreach ($proposal->lines as $line) {
                $order->lines()->create([
                    'product_id' => $line->product_id,
                    'description' => $line->description,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unit_price,
                    'tax_rate' => $line->tax_rate,
                    'discount_rate' => $line->discount_rate,
                    'total_amount' => $line->total_amount,
                ]);
            }

            $this->calculateTotals($order);

            return $order;
        });
    }

    public function confirmOrder(Order $order): void
    {
        if ($order->status !== 'draft') {
            return;
        }

        DB::transaction(function () use ($order) {
            $order->status = 'confirmed';
            $order->save();

            // Reserve stock
            $defaultWarehouse = \App\Modules\Inventory\Models\Warehouse::where('is_default', true)->first();
            $warehouseId = $defaultWarehouse ? $defaultWarehouse->id : 1;

            foreach ($order->lines as $line) {
                if ($line->product_id) {
                    $this->stockService->reserve($line->product_id, $warehouseId, $line->quantity, $order);
                }
            }
        });
    }

    public function deliverOrder(Order $order): void
    {
        if ($order->status !== 'confirmed' && $order->status !== 'processing') {
            // can only deliver if confirmed or processing
            // return;
        }

        DB::transaction(function () use ($order) {
            $order->status = 'delivered';
            $order->save();

            $defaultWarehouse = \App\Modules\Inventory\Models\Warehouse::where('is_default', true)->first();
            $warehouseId = $defaultWarehouse ? $defaultWarehouse->id : 1;

            foreach ($order->lines as $line) {
                if ($line->product_id) {
                    // Reduce physical stock
                    $this->stockService->removeStock($line->product_id, $warehouseId, $line->quantity, 'Order Delivery ' . $order->reference, $order);
                    // Release reservation (as it's now consumed)
                    $this->stockService->release($line->product_id, $warehouseId, $line->quantity);
                }
            }
        });
    }

    public function cancelOrder(Order $order): void
    {
        if ($order->status === 'cancelled' || $order->status === 'delivered') {
            return;
        }

        $previousStatus = $order->status;

        DB::transaction(function () use ($order, $previousStatus) {
            $order->status = 'cancelled';
            $order->save();

            // If it was reserved (confirmed/processing), release reservation
            if (in_array($previousStatus, ['confirmed', 'processing'])) {
                $defaultWarehouse = \App\Modules\Inventory\Models\Warehouse::where('is_default', true)->first();
                $warehouseId = $defaultWarehouse ? $defaultWarehouse->id : 1;

                foreach ($order->lines as $line) {
                    if ($line->product_id) {
                        $this->stockService->release($line->product_id, $warehouseId, $line->quantity);
                    }
                }
            }
        });
    }
}
