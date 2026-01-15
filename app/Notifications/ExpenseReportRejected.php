<?php

namespace App\Notifications;

use App\Modules\HR\Models\ExpenseReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpenseReportRejected extends Notification implements ShouldQueue
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
        $mail = (new MailMessage)
            ->subject('Votre note de frais a ete refusee')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Votre note de frais a ete refusee.')
            ->line('Titre: ' . $this->expenseReport->title)
            ->line('Montant: ' . number_format($this->expenseReport->total_amount, 2, ',', ' ') . ' FCFA');

        if ($this->expenseReport->rejection_reason) {
            $mail->line('Motif: ' . $this->expenseReport->rejection_reason);
        }

        return $mail->action('Voir la note de frais', route('hr.expenses.show', $this->expenseReport));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => 'Votre note de frais a ete refusee',
            'subtitle' => $this->expenseReport->title,
            'icon' => 'cancel',
            'color' => 'bg-red-500/20',
            'icon_color' => 'text-red-400',
            'url' => route('hr.expenses.show', $this->expenseReport),
            'expense_report_id' => $this->expenseReport->id,
        ];
    }
}
