<?php

namespace App\Notifications;

use App\Modules\Productivity\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ProjectMemberAdded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Project $project,
        public string $assignerName,
        public ?string $role = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $message = 'Vous avez ete ajoute au projet: ' . $this->project->name;
        if ($this->role) {
            $message .= ' (Role: ' . $this->role . ')';
        }

        return [
            'message' => $message,
            'subtitle' => 'Ajoute par ' . $this->assignerName,
            'icon' => 'group_add',
            'color' => 'bg-green-500/20',
            'icon_color' => 'text-green-400',
            'url' => route('productivity.projects.show', $this->project),
            'project_id' => $this->project->id,
        ];
    }
}
