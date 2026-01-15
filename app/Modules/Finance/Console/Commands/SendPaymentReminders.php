<?php

namespace App\Modules\Finance\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Finance\Services\ReminderService;

class SendPaymentReminders extends Command
{
    protected $signature = 'invoices:send-reminders';
    protected $description = 'Send payment reminders for overdue invoices';

    public function handle(ReminderService $service): void
    {
        $this->info('Starting payment reminders process...');

        $service->processReminders();

        $this->info('Payment reminders processed successfully.');
    }
}
