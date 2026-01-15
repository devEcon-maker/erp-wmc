<?php

namespace App\Notifications;

use App\Modules\HR\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestApproved extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('Votre demande de conge a ete approuvee')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Votre demande de conge a ete approuvee.')
            ->line('Type: ' . $this->leaveRequest->leaveType->name)
            ->line('Du ' . $this->leaveRequest->start_date->format('d/m/Y') . ' au ' . $this->leaveRequest->end_date->format('d/m/Y'))
            ->line('Duree: ' . $this->leaveRequest->days_count . ' jour(s)')
            ->action('Voir mes conges', route('hr.leaves.index'))
            ->line('Bonne vacances!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => 'Votre demande de conge a ete approuvee',
            'subtitle' => $this->leaveRequest->leaveType->name . ' - ' . $this->leaveRequest->start_date->format('d/m/Y') . ' au ' . $this->leaveRequest->end_date->format('d/m/Y'),
            'icon' => 'check_circle',
            'color' => 'bg-green-500/20',
            'icon_color' => 'text-green-400',
            'url' => route('hr.leaves.index'),
            'leave_request_id' => $this->leaveRequest->id,
        ];
    }
}
