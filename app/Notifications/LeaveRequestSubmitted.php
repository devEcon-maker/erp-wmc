<?php

namespace App\Notifications;

use App\Modules\HR\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestSubmitted extends Notification implements ShouldQueue
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
            ->subject('Nouvelle demande de conge a approuver')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line($this->leaveRequest->employee->full_name . ' a soumis une demande de conge.')
            ->line('Type: ' . $this->leaveRequest->leaveType->name)
            ->line('Du ' . $this->leaveRequest->start_date->format('d/m/Y') . ' au ' . $this->leaveRequest->end_date->format('d/m/Y'))
            ->line('Duree: ' . $this->leaveRequest->days_count . ' jour(s)')
            ->action('Voir la demande', route('hr.leaves.index'))
            ->line('Merci de traiter cette demande dans les meilleurs delais.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->leaveRequest->employee->full_name . ' a soumis une demande de conge',
            'subtitle' => $this->leaveRequest->leaveType->name . ' - ' . $this->leaveRequest->days_count . ' jour(s)',
            'icon' => 'event_busy',
            'color' => 'bg-yellow-500/20',
            'icon_color' => 'text-yellow-400',
            'url' => route('hr.leaves.index'),
            'leave_request_id' => $this->leaveRequest->id,
        ];
    }
}
