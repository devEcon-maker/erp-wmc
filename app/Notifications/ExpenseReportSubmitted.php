<?php

namespace App\Notifications;

use App\Modules\HR\Models\ExpenseReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpenseReportSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ExpenseReport $expenseReport
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouvelle note de frais a approuver')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line($this->expenseReport->employee->full_name . ' a soumis une note de frais.')
            ->line('Titre: ' . $this->expenseReport->title)
            ->line('Montant: ' . number_format($this->expenseReport->total_amount, 2, ',', ' ') . ' FCFA')
            ->action('Voir la note de frais', route('hr.expenses.show', $this->expenseReport))
            ->line('Merci de traiter cette demande dans les meilleurs delais.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->expenseReport->employee->full_name . ' a soumis une note de frais',
            'subtitle' => $this->expenseReport->title . ' - ' . number_format($this->expenseReport->total_amount, 2, ',', ' ') . ' FCFA',
            'icon' => 'receipt_long',
            'color' => 'bg-orange-500/20',
            'icon_color' => 'text-orange-400',
            'url' => route('hr.expenses.show', $this->expenseReport),
            'expense_report_id' => $this->expenseReport->id,
        ];
    }
}
