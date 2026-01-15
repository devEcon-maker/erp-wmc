<?php

namespace App\Modules\Core\Services;

use App\Modules\CRM\Models\Contact;
use App\Modules\CRM\Models\Opportunity;
use App\Modules\CRM\Models\OpportunityStage;
use App\Modules\CRM\Models\Order;
use App\Modules\Finance\Models\Invoice;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\HR\Models\ExpenseReport;
use App\Modules\HR\Models\Employee;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\StockLevel;
use App\Modules\Productivity\Models\Project;
use App\Modules\Productivity\Models\Task;
use App\Modules\Agenda\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    public function getStatCards(): array
    {
        $user = Auth::user();
        $stats = [];

        // CA du mois (si permission invoices.view)
        if ($user->can('invoices.view')) {
            $stats['monthly_revenue'] = Invoice::where('status', 'paid')
                ->whereMonth('paid_at', Carbon::now()->month)
                ->whereYear('paid_at', Carbon::now()->year)
                ->sum('total_amount_ttc');
        }

        // Factures en attente (si permission invoices.view)
        if ($user->can('invoices.view')) {
            $stats['pending_invoices'] = Invoice::whereIn('status', ['sent', 'overdue'])->count();
            $stats['pending_invoices_amount'] = Invoice::whereIn('status', ['sent', 'overdue'])
                ->selectRaw('SUM(total_amount_ttc - paid_amount) as total')
                ->value('total') ?? 0;
        }

        // Commandes en cours (si permission orders.view)
        if ($user->can('orders.view')) {
            $stats['active_orders'] = Order::whereIn('status', ['confirmed', 'processing', 'shipped'])
                ->count();
        }

        // Tâches à faire (de l'utilisateur)
        $employee = $user->employee;
        if ($employee) {
            $stats['my_tasks'] = Task::where('assigned_to', $employee->id)
                ->whereIn('status', ['todo', 'in_progress'])
                ->count();

            $stats['overdue_tasks'] = Task::where('assigned_to', $employee->id)
                ->whereIn('status', ['todo', 'in_progress'])
                ->where('due_date', '<', Carbon::now())
                ->count();
        } else {
            $stats['my_tasks'] = 0;
            $stats['overdue_tasks'] = 0;
        }

        return $stats;
    }

    public function getOpportunityPipeline(): array
    {
        $stages = OpportunityStage::orderBy('order')->get();
        $pipeline = [];

        foreach ($stages as $stage) {
            $opportunities = Opportunity::where('stage_id', $stage->id)->get();
            $pipeline[] = [
                'id' => $stage->id,
                'name' => $stage->name,
                'color' => $stage->color,
                'count' => $opportunities->count(),
                'amount' => $opportunities->sum('amount'),
                'weighted_amount' => $opportunities->sum(function ($opp) use ($stage) {
                    return $opp->amount * ($stage->probability / 100);
                }),
            ];
        }

        return $pipeline;
    }

    public function getUpcomingEvents(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        $user = Auth::user();
        $employee = $user->employee;

        return Event::where('start_at', '>=', Carbon::now())
            ->where(function ($query) use ($user, $employee) {
                $query->where('created_by', $user->id);
                if ($employee) {
                    $query->orWhereHas('attendees', function ($q) use ($employee) {
                        $q->where('employee_id', $employee->id);
                    });
                }
            })
            ->orderBy('start_at')
            ->limit($limit)
            ->get();
    }

    public function getRecentActivity(int $limit = 10): array
    {
        $activities = collect();

        // Dernières factures créées
        $invoices = Invoice::with('contact')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($invoice) {
                return [
                    'type' => 'invoice',
                    'icon' => 'receipt',
                    'color' => 'blue',
                    'message' => "Facture {$invoice->reference} créée",
                    'subtitle' => $invoice->contact?->display_name,
                    'url' => route('finance.invoices.show', $invoice),
                    'created_at' => $invoice->created_at,
                ];
            });
        $activities = $activities->merge($invoices);

        // Dernières commandes
        $orders = Order::with('contact')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'type' => 'order',
                    'icon' => 'shopping_cart',
                    'color' => 'green',
                    'message' => "Commande {$order->reference} créée",
                    'subtitle' => $order->contact?->display_name,
                    'url' => route('crm.orders.show', $order),
                    'created_at' => $order->created_at,
                ];
            });
        $activities = $activities->merge($orders);

        // Nouveaux contacts
        $contacts = Contact::latest()
            ->limit(5)
            ->get()
            ->map(function ($contact) {
                return [
                    'type' => 'contact',
                    'icon' => 'person_add',
                    'color' => 'primary',
                    'message' => "Nouveau contact: {$contact->display_name}",
                    'subtitle' => ucfirst($contact->type),
                    'url' => route('crm.contacts.show', $contact),
                    'created_at' => $contact->created_at,
                ];
            });
        $activities = $activities->merge($contacts);

        // Nouveaux projets
        $projects = Project::latest()
            ->limit(5)
            ->get()
            ->map(function ($project) {
                return [
                    'type' => 'project',
                    'icon' => 'folder',
                    'color' => 'purple',
                    'message' => "Nouveau projet: {$project->name}",
                    'subtitle' => $project->client?->display_name,
                    'url' => route('productivity.projects.show', $project),
                    'created_at' => $project->created_at,
                ];
            });
        $activities = $activities->merge($projects);

        return $activities->sortByDesc('created_at')->take($limit)->values()->toArray();
    }

    public function getPendingApprovals(): array
    {
        $user = Auth::user();
        $approvals = [];

        // Demandes de congés en attente (si manager/rh/admin)
        if ($user->hasAnyRole(['admin', 'rh', 'manager'])) {
            $employee = $user->employee;

            $leaveQuery = LeaveRequest::where('status', 'pending');

            // Si manager, seulement ses subordonnés
            if ($user->hasRole('manager') && $employee) {
                $subordinateIds = Employee::where('manager_id', $employee->id)->pluck('id');
                $leaveQuery->whereIn('employee_id', $subordinateIds);
            }

            $approvals['leaves'] = $leaveQuery->with('employee')->get();
        }

        // Notes de frais à approuver (si admin)
        if ($user->hasAnyRole(['admin', 'comptable'])) {
            $approvals['expenses'] = ExpenseReport::where('status', 'submitted')
                ->with('employee')
                ->get();
        }

        return $approvals;
    }

    public function getMyTasks(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        return Task::with(['project'])
            ->where('assigned_to', $employee->id)
            ->whereIn('status', ['todo', 'in_progress', 'review'])
            ->orderByRaw("CASE WHEN due_date < NOW() THEN 0 ELSE 1 END")
            ->orderBy('due_date')
            ->limit($limit)
            ->get();
    }

    public function getStockAlerts(): \Illuminate\Database\Eloquent\Collection
    {
        return Product::with(['stockLevels'])
            ->where('track_stock', true)
            ->where('min_stock_alert', '>', 0)
            ->get()
            ->filter(function ($product) {
                $totalStock = $product->stockLevels->sum('quantity');
                return $totalStock <= $product->min_stock_alert;
            })
            ->take(10);
    }

    /**
     * Get financial flow data for chart (last 6 months)
     */
    public function getFinancialFlowData(): array
    {
        $months = [];
        $revenues = [];
        $expenses = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->translatedFormat('M Y');

            // Recettes (factures payées)
            $monthRevenue = Invoice::where('status', 'paid')
                ->whereMonth('paid_at', $date->month)
                ->whereYear('paid_at', $date->year)
                ->sum('total_amount_ttc');
            $revenues[] = round($monthRevenue, 2);

            // Dépenses (notes de frais approuvées)
            $monthExpense = ExpenseReport::where('status', 'approved')
                ->whereMonth('approved_at', $date->month)
                ->whereYear('approved_at', $date->year)
                ->sum('total_amount');
            $expenses[] = round($monthExpense, 2);
        }

        return [
            'labels' => $months,
            'revenues' => $revenues,
            'expenses' => $expenses,
        ];
    }

    /**
     * Get today's events
     */
    public function getTodayEvents(): \Illuminate\Database\Eloquent\Collection
    {
        $user = Auth::user();
        $today = Carbon::today();

        return Event::where(function ($query) use ($today) {
                $query->whereDate('start_at', $today)
                    ->orWhere(function ($q) use ($today) {
                        $q->where('start_at', '<=', $today->endOfDay())
                          ->where('end_at', '>=', $today->startOfDay());
                    });
            })
            ->orderBy('start_at')
            ->limit(10)
            ->get();
    }

    /**
     * Get latest prospects
     */
    public function getLatestProspects(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return Contact::where('type', 'prospect')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
