<?php

namespace App\Notifications;

use App\Modules\HR\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LeaveRequest $leaveRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Votre demande de conge a ete refusee')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Votre demande de conge a ete refusee.')
            ->line('Type: ' . $this->leaveRequest->leaveType->name)
            ->line('Du ' . $this->leaveRequest->start_date->format('d/m/Y') . ' au ' . $this->leaveRequest->end_date->format('d/m/Y'));

        if ($this->leaveRequest->rejection_reason) {
            $mail->line('Motif: ' . $this->leaveRequest->rejection_reason);
        }

        return $mail->action('Voir mes conges', route('hr.leaves.index'));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => 'Votre demande de conge a ete refusee',
            'subtitle' => $this->leaveRequest->leaveType->name . ' - ' . $this->leaveRequest->start_date->format('d/m/Y'),
            'icon' => 'cancel',
            'color' => 'bg-red-500/20',
            'icon_color' => 'text-red-400',
            'url' => route('hr.leaves.index'),
            'leave_request_id' => $this->leaveRequest->id,
        ];
    }
}
