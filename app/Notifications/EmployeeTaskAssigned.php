<?php

namespace App\Notifications;

use App\Modules\HR\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class EmployeeTaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Task $task,
        public string $assignerName
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => 'Vous avez ete ajoute a la tache: ' . $this->task->title,
            'subtitle' => 'Assigne par ' . $this->assignerName,
            'icon' => 'assignment_ind',
            'color' => 'bg-blue-500/20',
            'icon_color' => 'text-blue-400',
            'url' => route('hr.tasks.show', $this->task),
            'task_id' => $this->task->id,
        ];
    }
}
