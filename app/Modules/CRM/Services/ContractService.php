<?php

namespace App\Modules\CRM\Services;

use App\Modules\CRM\Models\Contract;
use Illuminate\Support\Str;

class ContractService
{
    public function generateReference(): string
    {
        // Format: CTR-YYYYMM-XXXX
        $prefix = 'CTR-' . date('Ym') . '-';
        $lastContract = Contract::where('reference', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastContract) {
            $lastNumber = intval(substr($lastContract->reference, strlen($prefix)));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function calculateTotals(Contract $contract): void
    {
        $lines = $contract->lines;

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

        $contract->total_amount = $totalAmount;
        $contract->discount_amount = $discountAmount;
        $contract->tax_amount = $taxAmount;
        $contract->total_amount_ttc = ($totalAmount - $discountAmount) + $taxAmount;

        $contract->save();
    }

    public function activate(Contract $contract): void
    {
        if ($contract->status === 'active')
            return;

        $contract->status = 'active';
        $contract->next_billing_date = $this->calculateNextBillingDate($contract);
        $contract->save();
    }

    public function calculateNextBillingDate(Contract $contract): ?string // Returns date string or null
    {
        if ($contract->billing_frequency === 'once')
            return null;

        $startDate = $contract->start_date;
        // Logic: if next_billing_date is null (new contract), start from start_date
        // Otherwise calculate from last billing date (not tracked here yet, simplified)

        return $startDate->format('Y-m-d'); // For first invoice, it is the start date
    }

    public function advanceBillingDate(Contract $contract): void
    {
        if (!$contract->next_billing_date)
            return;

        $currentDate = $contract->next_billing_date; // Carbon object via cast

        switch ($contract->billing_frequency) {
            case 'monthly':
                $nextDate = $currentDate->copy()->addMonth();
                break;
            case 'quarterly':
                $nextDate = $currentDate->copy()->addMonths(3);
                break;
            case 'yearly':
                $nextDate = $currentDate->copy()->addYear();
                break;
            default:
                $nextDate = null;
        }

        // Check if next date is beyond end date
        if ($contract->end_date && $nextDate && $nextDate->gt($contract->end_date)) {
            $nextDate = null;
            // Maybe expire contract here?
        }

        $contract->next_billing_date = $nextDate;
        $contract->save();
    }
}
