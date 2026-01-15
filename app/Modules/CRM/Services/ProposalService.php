<?php

namespace App\Modules\CRM\Services;

use App\Modules\CRM\Models\Proposal;
use App\Modules\CRM\Models\ProposalLine;
use Illuminate\Support\Str;

class ProposalService
{
    public function generateReference(): string
    {
        // Format: PROP-YYYYMM-XXXX (e.g., PROP-202401-0001)
        $prefix = 'PROP-' . date('Ym') . '-';
        $lastProposal = Proposal::where('reference', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastProposal) {
            $lastNumber = intval(substr($lastProposal->reference, strlen($prefix)));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function calculateTotals(Proposal $proposal): void
    {
        $lines = $proposal->lines;

        $totalAmount = 0; // HT
        $taxAmount = 0;
        $discountAmount = 0;

        foreach ($lines as $line) {
            // Line Calculation
            // Line Total = Qty * Unit Price * (1 - Discount Rate / 100)
            // But we store calculated total in line for snapshot

            $lineTotal = $line->quantity * $line->unit_price;
            $lineDiscount = $lineTotal * ($line->discount_rate / 100);
            $lineTotalAfterDiscount = $lineTotal - $lineDiscount;

            // Tax is on the discounted amount
            $lineTax = $lineTotalAfterDiscount * ($line->tax_rate / 100);

            $totalAmount += $lineTotal;
            $discountAmount += $lineDiscount;
            $taxAmount += $lineTax;
        }

        $proposal->total_amount = $totalAmount;
        $proposal->discount_amount = $discountAmount;
        $proposal->tax_amount = $taxAmount;
        $proposal->total_amount_ttc = ($totalAmount - $discountAmount) + $taxAmount;

        $proposal->save();
    }
}
