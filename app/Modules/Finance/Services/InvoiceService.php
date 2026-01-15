<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\Invoice;
use App\Modules\CRM\Models\Order;
use App\Modules\CRM\Models\Contract;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function generateReference(): string
    {
        // Format: FAC-YYYY-XXXXX (e.g., FAC-2024-00001) strictly sequential
        $year = date('Y');
        $prefix = 'FAC-' . $year . '-';

        $lastInvoice = Invoice::where('reference', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->reference, strlen($prefix)));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    public function calculateTotals(Invoice $invoice): void
    {
        $lines = $invoice->lines;

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

        $invoice->total_amount = $totalAmount;
        $invoice->discount_amount = $discountAmount;
        $invoice->tax_amount = $taxAmount;
        $invoice->total_amount_ttc = ($totalAmount - $discountAmount) + $taxAmount;

        // Update Paid Amount & Status based on payments
        $invoice->paid_amount = $invoice->payments->sum('amount');

        if ($invoice->paid_amount >= $invoice->total_amount_ttc) {
            $invoice->status = 'paid';
            $invoice->paid_at = $invoice->payments->max('payment_date') ?? now();
        } elseif ($invoice->paid_amount > 0) {
            $invoice->status = 'partial';
        } else {
            // Keep existing status if not paid (draft, sent, overdue)
            if ($invoice->status === 'paid' || $invoice->status === 'partial') {
                $invoice->status = 'sent'; // Revert if payment deleted
            }
        }

        $invoice->save();
    }

    public function createFromOrder(Order $order): Invoice
    {
        return DB::transaction(function () use ($order) {
            $invoice = new Invoice();
            $invoice->contact_id = $order->contact_id;
            $invoice->order_id = $order->id;
            $invoice->reference = $this->generateReference();
            $invoice->status = 'draft';
            $invoice->order_date = now();
            $invoice->due_date = now()->addDays(30); // Configurable later
            $invoice->created_by = auth()->id();
            $invoice->notes = $order->notes;
            $invoice->save();

            foreach ($order->lines as $line) {
                $invoice->lines()->create([
                    'product_id' => $line->product_id,
                    'description' => $line->description,
                    'quantity' => $line->quantity, // Should be undelivered qty ideally
                    'unit_price' => $line->unit_price,
                    'tax_rate' => $line->tax_rate,
                    'discount_rate' => $line->discount_rate,
                    'total_amount' => $line->total_amount,
                ]);
            }

            $this->calculateTotals($invoice);

            return $invoice;
        });
    }

    public function registerPayment(Invoice $invoice, float $amount, string $method, ?string $reference, ?string $notes = null): void
    {
        $invoice->payments()->create([
            'amount' => $amount,
            'payment_date' => now(),
            'payment_method' => $method,
            'reference' => $reference,
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);

        $this->calculateTotals($invoice);
    }
}
