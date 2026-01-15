<?php

namespace App\Notifications;

use App\Modules\Productivity\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Task $task
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Nouvelle tache assignee: ' . $this->task->title)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Une nouvelle tache vous a ete assignee.')
            ->line('Titre: ' . $this->task->title)
            ->line('Projet: ' . $this->task->project->name)
            ->line('Priorite: ' . ucfirst($this->task->priority));

        if ($this->task->due_date) {
            $mail->line('Echeance: ' . $this->task->due_date->format('d/m/Y'));
        }

        return $mail->action('Voir la tache', route('productivity.tasks.show', $this->task));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => 'Nouvelle tache: ' . $this->task->title,
            'subtitle' => $this->task->project->name . ($this->task->due_date ? ' - Echeance: ' . $this->task->due_date->format('d/m') : ''),
            'icon' => 'task_alt',
            'color' => 'bg-primary/20',
            'icon_color' => 'text-primary',
            'url' => route('productivity.tasks.show', $this->task),
            'task_id' => $this->task->id,
        ];
    }
}
