<?php

namespace App\Notifications;

use App\Modules\HR\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewJobApplication extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public JobApplication $application
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouvelle candidature recue: ' . $this->application->jobPosition->title)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Une nouvelle candidature a ete recue.')
            ->line('Poste: ' . $this->application->jobPosition->title)
            ->line('Candidat: ' . $this->application->full_name)
            ->line('Email: ' . $this->application->email)
            ->action('Voir la candidature', route('hr.recruitment.applications.show', $this->application))
            ->line('Merci de traiter cette candidature dans les meilleurs delais.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => 'Nouvelle candidature: ' . $this->application->full_name,
            'subtitle' => $this->application->jobPosition->title,
            'icon' => 'work',
            'color' => 'bg-purple-500/20',
            'icon_color' => 'text-purple-400',
            'url' => route('hr.recruitment.applications.show', $this->application),
            'application_id' => $this->application->id,
        ];
    }
}
