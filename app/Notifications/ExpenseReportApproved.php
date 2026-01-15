<?php

namespace App\Notifications;

use App\Modules\HR\Models\ExpenseReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpenseReportApproved extends Notification implements ShouldQueue
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
            ->subject('Votre note de frais a ete approuvee')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Votre note de frais a ete approuvee.')
            ->line('Titre: ' . $this->expenseReport->title)
            ->line('Montant: ' . number_format($this->expenseReport->total_amount, 2, ',', ' ') . ' FCFA')
            ->action('Voir la note de frais', route('hr.expenses.show', $this->expenseReport))
            ->line('Le remboursement sera effectue prochainement.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => 'Votre note de frais a ete approuvee',
            'subtitle' => $this->expenseReport->title . ' - ' . number_format($this->expenseReport->total_amount, 2, ',', ' ') . ' FCFA',
            'icon' => 'check_circle',
            'color' => 'bg-green-500/20',
            'icon_color' => 'text-green-400',
            'url' => route('hr.expenses.show', $this->expenseReport),
            'expense_report_id' => $this->expenseReport->id,
        ];
    }
}
