<div class="space-y-6">
    <!-- Header -->
    <div class="bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <h2 class="text-2xl font-bold text-white">Tableau de Bord</h2>
        <p class="text-text-secondary text-sm">Bienvenue, {{ auth()->user()->name }}. Voici votre apercu de l'activite.</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @can('invoices.view')
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">CA du mois</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['monthly_revenue'] ?? 0, 2, ',', ' ') }} FCFA</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-green-400">payments</span>
                </div>
            </div>
        </div>

        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">Factures en attente</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['pending_invoices'] ?? 0 }}</p>
                    <p class="text-xs text-yellow-400">{{ number_format($stats['pending_invoices_amount'] ?? 0, 2, ',', ' ') }} FCFA</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-yellow-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-yellow-400">receipt_long</span>
                </div>
            </div>
        </div>
        @endcan

        @can('orders.view')
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">Commandes en cours</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['active_orders'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-400">shopping_cart</span>
                </div>
            </div>
        </div>
        @endcan

        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">Mes taches</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['my_tasks'] ?? 0 }}</p>
                    @if(($stats['overdue_tasks'] ?? 0) > 0)
                        <p class="text-xs text-red-400">{{ $stats['overdue_tasks'] }} en retard</p>
                    @endif
                </div>
                <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">task_alt</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column (2/3) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Financial Flow Chart -->
            @can('invoices.view')
            @if(!empty($financialFlow))
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">monitoring</span>
                        Flux Financier
                    </h3>
                    <span class="text-sm text-text-secondary">Recettes vs Depenses sur 6 mois</span>
                </div>
                <div class="h-64">
                    <canvas id="financialFlowChart"></canvas>
                </div>
            </div>
            @endif
            @endcan

            <!-- Opportunity Pipeline Mini -->
            @can('opportunities.view')
            @if(count($pipeline) > 0)
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">leaderboard</span>
                        Pipeline Commercial
                    </h3>
                    <a href="{{ route('crm.opportunities.index') }}" class="text-sm text-primary hover:text-primary/80">
                        Voir tout
                    </a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                    @foreach($pipeline as $stage)
                    <div class="text-center p-3 rounded-lg bg-surface-highlight">
                        <div class="w-8 h-8 rounded-full mx-auto mb-2 flex items-center justify-center text-white text-sm font-bold"
                            style="background-color: {{ $stage['color'] }}">
                            {{ $stage['count'] }}
                        </div>
                        <p class="text-xs text-text-secondary truncate">{{ $stage['name'] }}</p>
                        <p class="text-sm font-medium text-white">{{ number_format($stage['amount'], 0, ',', ' ') }} FCFA</p>
                    </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-4 border-t border-[#3a2e24] flex justify-between items-center">
                    <span class="text-sm text-text-secondary">CA pondere total:</span>
                    <span class="text-lg font-bold text-primary">
                        {{ number_format(collect($pipeline)->sum('weighted_amount'), 0, ',', ' ') }} FCFA
                    </span>
                </div>
            </div>
            @endif
            @endcan

            <!-- My Tasks -->
            @if($myTasks && $myTasks->count() > 0)
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">checklist</span>
                        Mes Taches
                    </h3>
                    <a href="{{ route('productivity.dashboard') }}" class="text-sm text-primary hover:text-primary/80">
                        Voir tout
                    </a>
                </div>
                <div class="space-y-2">
                    @foreach($myTasks as $task)
                    <div class="flex items-center justify-between p-3 rounded-lg hover:bg-surface-highlight group">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <button wire:click="markTaskDone({{ $task->id }})"
                                class="w-5 h-5 rounded border-2 border-[#3a2e24] hover:border-primary hover:bg-primary/20 flex items-center justify-center transition-colors">
                                <span class="material-symbols-outlined text-[14px] text-transparent group-hover:text-primary">check</span>
                            </button>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('productivity.tasks.show', $task) }}" class="text-sm text-white hover:text-primary truncate block">
                                    {{ $task->title }}
                                </a>
                                <p class="text-xs text-text-secondary">{{ $task->project?->name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($task->due_date)
                                <span class="text-xs px-2 py-1 rounded-full {{ $task->due_date < now() ? 'bg-red-500/20 text-red-400' : 'bg-surface-highlight text-text-secondary' }}">
                                    {{ $task->due_date->format('d/m') }}
                                </span>
                            @endif
                            <span class="text-xs px-2 py-1 rounded-full {{ $task->priority === 'urgent' ? 'bg-red-500/20 text-red-400' : ($task->priority === 'high' ? 'bg-orange-500/20 text-orange-400' : 'bg-surface-highlight text-text-secondary') }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Recent Activity -->
            @if(count($recentActivity) > 0)
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-primary">history</span>
                    Activite Recente
                </h3>
                <div class="space-y-4">
                    @foreach($recentActivity as $activity)
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                            {{ $activity['color'] === 'blue' ? 'bg-blue-500/20' : '' }}
                            {{ $activity['color'] === 'green' ? 'bg-green-500/20' : '' }}
                            {{ $activity['color'] === 'primary' ? 'bg-primary/20' : '' }}
                            {{ $activity['color'] === 'purple' ? 'bg-purple-500/20' : '' }}">
                            <span class="material-symbols-outlined text-[18px]
                                {{ $activity['color'] === 'blue' ? 'text-blue-400' : '' }}
                                {{ $activity['color'] === 'green' ? 'text-green-400' : '' }}
                                {{ $activity['color'] === 'primary' ? 'text-primary' : '' }}
                                {{ $activity['color'] === 'purple' ? 'text-purple-400' : '' }}">
                                {{ $activity['icon'] }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="{{ $activity['url'] }}" class="text-sm text-white hover:text-primary">
                                {{ $activity['message'] }}
                            </a>
                            @if($activity['subtitle'])
                                <p class="text-xs text-text-secondary">{{ $activity['subtitle'] }}</p>
                            @endif
                        </div>
                        <span class="text-xs text-text-secondary flex-shrink-0">
                            {{ $activity['created_at']->diffForHumans() }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column (1/3) -->
        <div class="space-y-6">
            <!-- Today's Agenda -->
            @can('events.view')
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">today</span>
                        Agenda du Jour
                    </h3>
                    <a href="{{ route('agenda.calendar') }}" class="text-sm text-primary hover:text-primary/80">
                        Calendrier
                    </a>
                </div>
                @if($todayEvents && $todayEvents->count() > 0)
                <div class="space-y-3">
                    @foreach($todayEvents as $event)
                    <div class="p-3 rounded-lg bg-surface-highlight border-l-4" style="border-left-color: {{ $event->color ?? '#E76F51' }}">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-white">{{ $event->title }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-surface-dark text-text-secondary">
                                {{ ucfirst($event->type ?? 'event') }}
                            </span>
                        </div>
                        <div class="text-xs text-text-secondary flex items-center gap-2">
                            <span class="material-symbols-outlined text-[14px]">schedule</span>
                            @if($event->all_day)
                                Toute la journee
                            @else
                                {{ $event->start_at->format('H:i') }} - {{ $event->end_at?->format('H:i') }}
                            @endif
                        </div>
                        @if($event->location)
                        <div class="text-xs text-text-secondary flex items-center gap-2 mt-1">
                            <span class="material-symbols-outlined text-[14px]">location_on</span>
                            {{ $event->location }}
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-text-secondary">
                    <span class="material-symbols-outlined text-4xl mb-2 block opacity-50">event_available</span>
                    <p class="text-sm">Aucun evenement aujourd'hui</p>
                </div>
                @endif
                <a href="{{ route('agenda.calendar') }}"
                    class="mt-4 w-full flex items-center justify-center gap-2 py-2 bg-primary/20 text-primary rounded-lg hover:bg-primary/30 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Nouvel evenement
                </a>
            </div>
            @endcan

            <!-- Latest Prospects -->
            @can('contacts.view')
            @if($latestProspects && $latestProspects->count() > 0)
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-400">person_search</span>
                        Derniers Prospects
                    </h3>
                    <a href="{{ route('crm.contacts.index', ['type' => 'prospect']) }}" class="text-sm text-primary hover:text-primary/80">
                        Voir tout
                    </a>
                </div>
                <div class="space-y-3">
                    @foreach($latestProspects as $prospect)
                    <a href="{{ route('crm.contacts.show', $prospect) }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface-highlight transition-colors">
                        <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center">
                            <span class="text-blue-400 font-bold text-sm">
                                {{ strtoupper(substr($prospect->first_name ?? $prospect->company_name ?? 'P', 0, 1)) }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-white truncate">
                                {{ $prospect->company_name ?? $prospect->first_name . ' ' . $prospect->last_name }}
                            </p>
                            <p class="text-xs text-text-secondary">{{ $prospect->city ?? 'Non renseigne' }}</p>
                        </div>
                        <span class="text-xs text-text-secondary">{{ $prospect->created_at->diffForHumans() }}</span>
                    </a>
                    @endforeach
                </div>
                <a href="{{ route('crm.contacts.create', ['type' => 'prospect']) }}"
                    class="mt-4 w-full flex items-center justify-center gap-2 py-2 bg-blue-500/20 text-blue-400 rounded-lg hover:bg-blue-500/30 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">person_add</span>
                    Nouveau prospect
                </a>
            </div>
            @endif
            @endcan

            <!-- Stock Alerts -->
            @can('stock.view')
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-red-400">inventory_2</span>
                        Alertes Stock
                    </h3>
                    <a href="{{ route('inventory.stock.dashboard') }}" class="text-sm text-primary hover:text-primary/80">
                        Voir tout
                    </a>
                </div>
                @if($stockAlerts && count($stockAlerts) > 0)
                <div class="space-y-2">
                    @foreach($stockAlerts as $product)
                    @php
                        $totalStock = $product->stockLevels->sum('quantity');
                    @endphp
                    <a href="{{ route('inventory.products.show', $product) }}" class="flex justify-between items-center p-3 rounded-lg bg-red-500/10 hover:bg-red-500/20 transition-colors">
                        <div>
                            <p class="text-sm text-white">{{ $product->name }}</p>
                            <p class="text-xs text-text-secondary">{{ $product->reference }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-red-400">{{ $totalStock }}</p>
                            <p class="text-xs text-text-secondary">min: {{ $product->min_stock_alert }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
                <a href="{{ route('inventory.purchases.create') }}"
                    class="mt-4 w-full flex items-center justify-center gap-2 py-2 bg-primary/20 text-primary rounded-lg hover:bg-primary/30 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">add_shopping_cart</span>
                    Commander stock
                </a>
                @else
                <div class="text-center py-8 text-text-secondary">
                    <span class="material-symbols-outlined text-4xl mb-2 block text-green-400 opacity-50">check_circle</span>
                    <p class="text-sm">Aucune alerte stock</p>
                </div>
                @endif
            </div>
            @endcan

            <!-- Pending Approvals - Leaves -->
            @if(!empty($pendingApprovals['leaves']) && $pendingApprovals['leaves']->count() > 0)
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-yellow-400">pending_actions</span>
                        Conges a Approuver
                    </h3>
                    <a href="{{ route('hr.leaves.index') }}" class="text-sm text-primary hover:text-primary/80">
                        Voir tout
                    </a>
                </div>
                <div class="space-y-2">
                    @foreach($pendingApprovals['leaves']->take(5) as $leave)
                    <a href="{{ route('hr.leaves.approval') }}" class="block p-3 rounded-lg hover:bg-surface-highlight transition-colors">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-white">{{ $leave->employee?->full_name }}</p>
                                <p class="text-xs text-text-secondary">{{ $leave->leaveType?->name }}</p>
                            </div>
                            <span class="text-xs bg-yellow-500/20 text-yellow-400 px-2 py-1 rounded-full">
                                {{ $leave->days_count }} j
                            </span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Expense Reports to Approve -->
            @if(!empty($pendingApprovals['expenses']) && $pendingApprovals['expenses']->count() > 0)
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-orange-400">receipt_long</span>
                        Frais a Approuver
                    </h3>
                    <a href="{{ route('hr.expenses.index') }}" class="text-sm text-primary hover:text-primary/80">
                        Voir tout
                    </a>
                </div>
                <div class="space-y-2">
                    @foreach($pendingApprovals['expenses']->take(5) as $expense)
                    <a href="{{ route('hr.expenses.show', $expense) }}" class="block p-3 rounded-lg hover:bg-surface-highlight transition-colors">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-white">{{ $expense->employee?->full_name }}</p>
                                <p class="text-xs text-text-secondary">{{ $expense->reference }}</p>
                            </div>
                            <span class="text-sm font-medium text-white">
                                {{ number_format($expense->total_amount, 2, ',', ' ') }} FCFA
                            </span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Upcoming Events -->
            @can('events.view')
            @if($upcomingEvents && $upcomingEvents->count() > 0)
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">event</span>
                        Prochains Evenements
                    </h3>
                    <a href="{{ route('agenda.calendar') }}" class="text-sm text-primary hover:text-primary/80">
                        Agenda
                    </a>
                </div>
                <div class="space-y-3">
                    @foreach($upcomingEvents as $event)
                    <div class="p-3 rounded-lg bg-surface-highlight">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-2 h-2 rounded-full" style="background-color: {{ $event->color }}"></span>
                            <span class="text-sm font-medium text-white">{{ $event->title }}</span>
                        </div>
                        <div class="text-xs text-text-secondary flex items-center gap-2">
                            <span class="material-symbols-outlined text-[14px]">schedule</span>
                            {{ $event->start_at->translatedFormat('d M H:i') }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endcan
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('financialFlowChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($financialFlow['labels'] ?? []),
                datasets: [
                    {
                        label: 'Recettes',
                        data: @json($financialFlow['revenues'] ?? []),
                        backgroundColor: 'rgba(34, 197, 94, 0.7)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: 'Depenses',
                        data: @json($financialFlow['expenses'] ?? []),
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#9ca3af',
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1f1912',
                        titleColor: '#fff',
                        bodyColor: '#9ca3af',
                        borderColor: '#3a2e24',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + new Intl.NumberFormat('fr-FR', {
                                    style: 'currency',
                                    currency: 'XAF'
                                }).format(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(58, 46, 36, 0.5)',
                        },
                        ticks: {
                            color: '#9ca3af'
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(58, 46, 36, 0.5)',
                        },
                        ticks: {
                            color: '#9ca3af',
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR', {
                                    style: 'currency',
                                    currency: 'XAF',
                                    maximumFractionDigits: 0
                                }).format(value);
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
