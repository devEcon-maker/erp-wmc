<?php

namespace App\Notifications;

use App\Modules\Agenda\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Event $event
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Rappel: ' . $this->event->title)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Rappel pour votre evenement a venir:')
            ->line('Titre: ' . $this->event->title);

        if ($this->event->all_day) {
            $mail->line('Date: ' . $this->event->start_at->format('d/m/Y') . ' (Toute la journee)');
        } else {
            $mail->line('Date: ' . $this->event->start_at->format('d/m/Y'))
                ->line('Heure: ' . $this->event->start_at->format('H:i') . ' - ' . $this->event->end_at->format('H:i'));
        }

        if ($this->event->location) {
            $mail->line('Lieu: ' . $this->event->location);
        }

        return $mail->action('Voir l\'evenement', route('agenda.calendar'));
    }

    public function toDatabase(object $notifiable): array
    {
        $time = $this->event->all_day
            ? 'Toute la journee'
            : $this->event->start_at->format('H:i');

        return [
            'message' => 'Rappel: ' . $this->event->title,
            'subtitle' => $this->event->start_at->format('d/m/Y') . ' - ' . $time,
            'icon' => 'event',
            'color' => 'bg-blue-500/20',
            'icon_color' => 'text-blue-400',
            'url' => route('agenda.calendar'),
            'event_id' => $this->event->id,
        ];
    }
}
