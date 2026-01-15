<?php

namespace App\Modules\Productivity\Livewire;

use App\Modules\Productivity\Models\Project;
use App\Modules\Productivity\Models\Task;
use App\Modules\Productivity\Models\TimeEntry;
use App\Modules\Productivity\Services\ProjectStatsService;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ProductivityDashboard extends Component
{
    public string $period = 'month';
    public ?Carbon $startDate = null;
    public ?Carbon $endDate = null;

    public function mount()
    {
        $this->setPeriodDates();
    }

    public function updatedPeriod()
    {
        $this->setPeriodDates();
    }

    private function setPeriodDates()
    {
        switch ($this->period) {
            case 'week':
                $this->startDate = now()->startOfWeek();
                $this->endDate = now()->endOfWeek();
                break;
            case 'month':
                $this->startDate = now()->startOfMonth();
                $this->endDate = now()->endOfMonth();
                break;
            case 'quarter':
                $this->startDate = now()->startOfQuarter();
                $this->endDate = now()->endOfQuarter();
                break;
            case 'year':
                $this->startDate = now()->startOfYear();
                $this->endDate = now()->endOfYear();
                break;
        }
    }

    public function getStatsProperty(): array
    {
        $statsService = app(ProjectStatsService::class);
        return $statsService->getGlobalStats($this->startDate, $this->endDate);
    }

    public function getMyTasksProperty()
    {
        $user = Auth::user();
        $employeeId = $user->employee?->id;

        if (!$employeeId) {
            return collect([]);
        }

        return Task::with('project')
            ->where('assigned_to', $employeeId)
            ->whereIn('status', ['todo', 'in_progress', 'review'])
            ->orderBy('due_date')
            ->limit(10)
            ->get();
    }

    public function getRecentProjectsProperty()
    {
        return Project::with(['manager', 'tasks'])
            ->whereIn('status', ['active', 'planning'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function getUpcomingDeadlinesProperty(): array
    {
        $statsService = app(ProjectStatsService::class);
        return $statsService->getUpcomingDeadlines(7);
    }

    public function getTimeByProjectProperty(): array
    {
        $statsService = app(ProjectStatsService::class);
        return $statsService->getTimeByProject($this->startDate, $this->endDate);
    }

    public function getDailyHoursProperty(): array
    {
        $statsService = app(ProjectStatsService::class);
        $start = $this->period === 'week' ? now()->subDays(7) : now()->subDays(30);
        return $statsService->getDailyHours($start, now());
    }

    public function getBudgetStatusProperty(): array
    {
        $statsService = app(ProjectStatsService::class);
        return $statsService->getProjectsBudgetStatus();
    }

    public function getMyTodayHoursProperty(): float
    {
        $user = Auth::user();
        $employeeId = $user->employee?->id;

        if (!$employeeId) {
            return 0;
        }

        return TimeEntry::where('employee_id', $employeeId)
            ->where('date', now()->format('Y-m-d'))
            ->sum('hours');
    }

    public function getMyWeekHoursProperty(): float
    {
        $user = Auth::user();
        $employeeId = $user->employee?->id;

        if (!$employeeId) {
            return 0;
        }

        return TimeEntry::where('employee_id', $employeeId)
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('hours');
    }

    public function render()
    {
        return view('livewire.productivity.productivity-dashboard', [
            'stats' => $this->stats,
            'myTasks' => $this->myTasks,
            'recentProjects' => $this->recentProjects,
            'upcomingDeadlines' => $this->upcomingDeadlines,
            'timeByProject' => $this->timeByProject,
            'dailyHours' => $this->dailyHours,
            'budgetStatus' => $this->budgetStatus,
            'myTodayHours' => $this->myTodayHours,
            'myWeekHours' => $this->myWeekHours,
        ])->layout('layouts.app');
    }
}
