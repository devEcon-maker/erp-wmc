<?php

namespace App\Notifications;

use App\Modules\Finance\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceOverdue extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Invoice $invoice
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $daysOverdue = $this->invoice->due_date->diffInDays(now());

        return (new MailMessage)
            ->subject('Facture en retard de paiement')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('La facture ' . $this->invoice->reference . ' est en retard de paiement.')
            ->line('Client: ' . $this->invoice->contact?->display_name)
            ->line('Montant du: ' . number_format($this->invoice->total_ttc - $this->invoice->paid_amount, 2, ',', ' ') . ' FCFA')
            ->line('Echeance: ' . $this->invoice->due_date->format('d/m/Y') . ' (' . $daysOverdue . ' jours de retard)')
            ->action('Voir la facture', route('finance.invoices.show', $this->invoice))
            ->line('Une relance devrait etre envoyee.');
    }

    public function toDatabase(object $notifiable): array
    {
        $daysOverdue = $this->invoice->due_date->diffInDays(now());

        return [
            'message' => 'Facture ' . $this->invoice->reference . ' en retard',
            'subtitle' => $this->invoice->contact?->display_name . ' - ' . $daysOverdue . ' jours de retard',
            'icon' => 'warning',
            'color' => 'bg-red-500/20',
            'icon_color' => 'text-red-400',
            'url' => route('finance.invoices.show', $this->invoice),
            'invoice_id' => $this->invoice->id,
        ];
    }
}
