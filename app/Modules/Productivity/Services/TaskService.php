<?php

namespace App\Modules\Productivity\Services;

use App\Modules\Productivity\Models\Task;
use App\Modules\Productivity\Models\Project;

class TaskService
{
    public function create(array $data): Task
    {
        // Auto-set order
        if (!isset($data['order'])) {
            $maxOrder = Task::where('project_id', $data['project_id'])
                ->where('status', $data['status'] ?? 'todo')
                ->max('order') ?? 0;
            $data['order'] = $maxOrder + 1;
        }

        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->fresh();
    }

    public function updateStatus(Task $task, string $newStatus): Task
    {
        $oldStatus = $task->status;

        // Mettre à jour l'ordre dans la nouvelle colonne
        if ($oldStatus !== $newStatus) {
            $maxOrder = Task::where('project_id', $task->project_id)
                ->where('status', $newStatus)
                ->max('order') ?? 0;

            $task->update([
                'status' => $newStatus,
                'order' => $maxOrder + 1,
            ]);

            // Réordonner l'ancienne colonne
            $this->reorderColumn($task->project_id, $oldStatus);
        }

        return $task->fresh();
    }

    public function updateOrder(Task $task, int $newOrder, ?string $newStatus = null): Task
    {
        $status = $newStatus ?? $task->status;

        $task->update([
            'status' => $status,
            'order' => $newOrder,
        ]);

        return $task;
    }

    public function reorderColumn(int $projectId, string $status): void
    {
        $tasks = Task::where('project_id', $projectId)
            ->where('status', $status)
            ->orderBy('order')
            ->get();

        foreach ($tasks as $index => $task) {
            $task->update(['order' => $index + 1]);
        }
    }

    public function reorderTasks(array $taskIds): void
    {
        foreach ($taskIds as $index => $taskId) {
            Task::where('id', $taskId)->update(['order' => $index + 1]);
        }
    }

    public function assignTo(Task $task, ?int $employeeId): Task
    {
        $task->update(['assigned_to' => $employeeId]);

        return $task;
    }

    public function complete(Task $task): Task
    {
        return $this->updateStatus($task, 'done');
    }

    public function duplicate(Task $task): Task
    {
        $newTask = $task->replicate();
        $newTask->title = $task->title . ' (copie)';
        $newTask->status = 'todo';
        $newTask->order = Task::where('project_id', $task->project_id)
            ->where('status', 'todo')
            ->max('order') + 1;
        $newTask->save();

        return $newTask;
    }

    public function createSubtask(Task $parentTask, array $data): Task
    {
        $data['project_id'] = $parentTask->project_id;
        $data['parent_id'] = $parentTask->id;

        return $this->create($data);
    }

    public function getTasksForProject(Project $project, ?string $status = null)
    {
        $query = $project->tasks()->with(['assignee', 'children'])->rootTasks();

        if ($status) {
            $query->where('status', $status);
        }

        return $query->ordered()->get();
    }

    public function getTasksForEmployee(int $employeeId, ?string $status = null)
    {
        $query = Task::with(['project', 'assignee'])
            ->where('assigned_to', $employeeId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('due_date')->orderBy('priority', 'desc')->get();
    }

    public function getOverdueTasks(?int $employeeId = null)
    {
        $query = Task::with(['project', 'assignee'])->overdue();

        if ($employeeId) {
            $query->where('assigned_to', $employeeId);
        }

        return $query->orderBy('due_date')->get();
    }

    public function getTasksByStatus(int $projectId): array
    {
        $statuses = ['todo', 'in_progress', 'review', 'done'];
        $result = [];

        foreach ($statuses as $status) {
            $result[$status] = Task::where('project_id', $projectId)
                ->where('status', $status)
                ->with(['assignee'])
                ->rootTasks()
                ->ordered()
                ->get();
        }

        return $result;
    }
}
