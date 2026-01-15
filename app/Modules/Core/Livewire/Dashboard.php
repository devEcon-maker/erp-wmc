<?php

namespace App\Modules\Core\Livewire;

use App\Modules\Core\Services\DashboardService;
use App\Modules\Productivity\Models\Task;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public array $stats = [];
    public array $pipeline = [];
    public $upcomingEvents;
    public array $recentActivity = [];
    public array $pendingApprovals = [];
    public $myTasks;
    public $stockAlerts;
    public array $financialFlow = [];
    public $todayEvents;
    public $latestProspects;

    public function mount(DashboardService $dashboardService): void
    {
        $this->loadDashboardData($dashboardService);
    }

    public function loadDashboardData(DashboardService $dashboardService): void
    {
        $this->stats = $dashboardService->getStatCards();

        if (auth()->user()->can('opportunities.view')) {
            $this->pipeline = $dashboardService->getOpportunityPipeline();
        }

        if (auth()->user()->can('events.view')) {
            $this->upcomingEvents = $dashboardService->getUpcomingEvents();
            $this->todayEvents = $dashboardService->getTodayEvents();
        }

        $this->recentActivity = $dashboardService->getRecentActivity();
        $this->pendingApprovals = $dashboardService->getPendingApprovals();
        $this->myTasks = $dashboardService->getMyTasks();

        if (auth()->user()->can('stock.view')) {
            $this->stockAlerts = $dashboardService->getStockAlerts();
        }

        if (auth()->user()->can('invoices.view')) {
            $this->financialFlow = $dashboardService->getFinancialFlowData();
        }

        if (auth()->user()->can('contacts.view')) {
            $this->latestProspects = $dashboardService->getLatestProspects();
        }
    }

    public function markTaskDone(int $taskId): void
    {
        $task = Task::findOrFail($taskId);
        $task->update(['status' => 'done']);

        $this->myTasks = app(DashboardService::class)->getMyTasks();
        $this->dispatch('task-completed');
    }

    public function render()
    {
        return view('livewire.core.dashboard');
    }
}
