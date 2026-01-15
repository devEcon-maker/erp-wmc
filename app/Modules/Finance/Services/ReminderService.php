<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\PaymentReminder;
use Illuminate\Support\Facades\Log;

class ReminderService
{
    // Configuration hardcoded for now, could be in Settings
    const FIRST_REMINDER_DAYS = 7;
    const SECOND_REMINDER_DAYS = 15;
    const FINAL_REMINDER_DAYS = 30;

    public function processReminders(): int
    {
        $overdueInvoices = Invoice::overdue()->get();
        $count = 0;

        foreach ($overdueInvoices as $invoice) {
            $daysOverdue = now()->diffInDays($invoice->due_date);

            if ($this->shouldSendReminder($invoice, $daysOverdue)) {
                $this->sendReminder($invoice, $this->determineReminderType($daysOverdue));
                $count++;
            }
        }

        return $count;
    }

    protected function shouldSendReminder(Invoice $invoice, int $daysOverdue): bool
    {
        // Simple logic: check exact days to avoid spamming
        // In production, might check "if not sent X reminder yet and days >= Y"

        $hasFirst = $invoice->paymentReminders()->where('type', 'first')->exists();
        $hasSecond = $invoice->paymentReminders()->where('type', 'second')->exists();
        $hasFinal = $invoice->paymentReminders()->where('type', 'final')->exists();

        if ($daysOverdue >= self::FINAL_REMINDER_DAYS && !$hasFinal) {
            return true;
        }

        if ($daysOverdue >= self::SECOND_REMINDER_DAYS && !$hasSecond && !$hasFinal) {
            return true;
        }

        if ($daysOverdue >= self::FIRST_REMINDER_DAYS && !$hasFirst && !$hasSecond && !$hasFinal) {
            return true;
        }

        return false;
    }

    protected function determineReminderType(int $daysOverdue): string
    {
        if ($daysOverdue >= self::FINAL_REMINDER_DAYS)
            return 'final';
        if ($daysOverdue >= self::SECOND_REMINDER_DAYS)
            return 'second';
        return 'first';
    }

    public function sendReminder(Invoice $invoice, string $type): void
    {
        // Simulate Email Sending
        Log::info("Sending {$type} reminder for Invoice {$invoice->reference} to {$invoice->contact->email}");

        PaymentReminder::create([
            'invoice_id' => $invoice->id,
            'type' => $type,
            'sent_at' => now(),
            'status' => 'sent',
        ]);

        // Could update Invoice status notes or something
    }
}
