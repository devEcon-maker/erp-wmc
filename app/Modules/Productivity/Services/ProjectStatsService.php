<?php

namespace App\Modules\Productivity\Services;

use App\Modules\Productivity\Models\Project;
use App\Modules\Productivity\Models\Task;
use App\Modules\Productivity\Models\TimeEntry;
use Illuminate\Support\Carbon;

class ProjectStatsService
{
    public function getProjectStats(Project $project): array
    {
        $tasks = $project->tasks;
        $timeEntries = $project->timeEntries;

        return [
            'tasks' => [
                'total' => $tasks->count(),
                'todo' => $tasks->where('status', 'todo')->count(),
                'in_progress' => $tasks->where('status', 'in_progress')->count(),
                'review' => $tasks->where('status', 'review')->count(),
                'done' => $tasks->where('status', 'done')->count(),
                'overdue' => $tasks->filter(fn($t) => $t->is_overdue)->count(),
                'progress' => $tasks->count() > 0
                    ? round(($tasks->where('status', 'done')->count() / $tasks->count()) * 100)
                    : 0,
            ],
            'time' => [
                'total_hours' => $timeEntries->sum('hours'),
                'billable_hours' => $timeEntries->where('billable', true)->sum('hours'),
                'total_cost' => $timeEntries->sum(fn($e) => $e->cost),
                'estimated_hours' => $tasks->sum('estimated_hours'),
            ],
            'finance' => [
                'budget' => $project->budget,
                'total_cost' => $project->total_cost,
                'revenue' => $project->revenue,
                'profit' => $project->profit,
                'profit_margin' => $project->profit_margin,
            ],
            'team' => [
                'members_count' => $project->members->count(),
                'members' => $project->members->map(fn($m) => [
                    'id' => $m->id,
                    'name' => $m->full_name,
                    'tasks_count' => $tasks->where('assigned_to', $m->id)->count(),
                    'hours' => $timeEntries->where('employee_id', $m->id)->sum('hours'),
                ]),
            ],
        ];
    }

    public function getGlobalStats(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $projects = Project::all();
        $tasks = Task::whereBetween('created_at', [$startDate, $endDate])->get();
        $timeEntries = TimeEntry::whereBetween('date', [$startDate, $endDate])->get();

        return [
            'projects' => [
                'total' => $projects->count(),
                'active' => $projects->where('status', 'active')->count(),
                'planning' => $projects->where('status', 'planning')->count(),
                'completed' => $projects->where('status', 'completed')->count(),
                'on_hold' => $projects->where('status', 'on_hold')->count(),
            ],
            'tasks' => [
                'created' => $tasks->count(),
                'completed' => $tasks->where('status', 'done')->count(),
                'overdue' => Task::whereNotNull('due_date')
                    ->where('due_date', '<', now())
                    ->whereIn('status', ['todo', 'in_progress', 'review'])
                    ->count(),
            ],
            'time' => [
                'total_hours' => $timeEntries->sum('hours'),
                'billable_hours' => $timeEntries->where('billable', true)->sum('hours'),
                'total_cost' => $timeEntries->sum(fn($e) => $e->cost),
            ],
            'finance' => [
                'total_budget' => $projects->sum('budget'),
                'total_cost' => $projects->sum(fn($p) => $p->total_cost),
                'total_revenue' => $projects->sum(fn($p) => $p->revenue),
            ],
        ];
    }

    public function getTimeByProject(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        return TimeEntry::selectRaw('project_id, SUM(hours) as total_hours')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('project_id')
            ->with('project:id,name')
            ->get()
            ->map(fn($e) => [
                'project_id' => $e->project_id,
                'project_name' => $e->project?->name ?? 'Sans projet',
                'hours' => round($e->total_hours, 1),
            ])
            ->sortByDesc('hours')
            ->values()
            ->toArray();
    }

    public function getTimeByEmployee(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        return TimeEntry::selectRaw('employee_id, SUM(hours) as total_hours')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('employee_id')
            ->with('employee:id,first_name,last_name')
            ->get()
            ->map(fn($e) => [
                'employee_id' => $e->employee_id,
                'employee_name' => $e->employee?->full_name ?? 'Inconnu',
                'hours' => round($e->total_hours, 1),
            ])
            ->sortByDesc('hours')
            ->values()
            ->toArray();
    }

    public function getDailyHours(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        $entries = TimeEntry::selectRaw('date, SUM(hours) as total_hours')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy(fn($e) => $e->date->format('Y-m-d'));

        $result = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $dateKey = $current->format('Y-m-d');
            $result[] = [
                'date' => $dateKey,
                'hours' => round($entries->get($dateKey)?->total_hours ?? 0, 1),
            ];
            $current->addDay();
        }

        return $result;
    }

    public function getTasksCompletionTrend(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        $completed = Task::where('status', 'done')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->selectRaw('DATE(updated_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $result = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $dateKey = $current->format('Y-m-d');
            $result[] = [
                'date' => $dateKey,
                'completed' => $completed->get($dateKey)?->count ?? 0,
            ];
            $current->addDay();
        }

        return $result;
    }

    public function getProjectsBudgetStatus(): array
    {
        return Project::whereNotNull('budget')
            ->where('budget', '>', 0)
            ->whereIn('status', ['active', 'planning'])
            ->get()
            ->map(function ($project) {
                $usage = $project->budget > 0
                    ? round(($project->total_cost / $project->budget) * 100)
                    : 0;

                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'budget' => $project->budget,
                    'cost' => $project->total_cost,
                    'usage' => $usage,
                    'status' => $usage > 100 ? 'over' : ($usage > 80 ? 'warning' : 'ok'),
                ];
            })
            ->sortByDesc('usage')
            ->values()
            ->toArray();
    }

    public function getUpcomingDeadlines(int $days = 7): array
    {
        return Task::with(['project', 'assignee'])
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now(), now()->addDays($days)])
            ->whereIn('status', ['todo', 'in_progress', 'review'])
            ->orderBy('due_date')
            ->get()
            ->map(fn($task) => [
                'id' => $task->id,
                'title' => $task->title,
                'project_id' => $task->project_id,
                'project_name' => $task->project->name,
                'assignee' => $task->assignee?->full_name,
                'due_date' => $task->due_date->format('Y-m-d'),
                'days_left' => (int) now()->startOfDay()->diffInDays($task->due_date->startOfDay()),
            ])
            ->toArray();
    }
}
