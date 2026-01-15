<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white">Dashboard Productivité</h2>
            <p class="text-text-secondary text-sm">Vue d'ensemble de vos projets et tâches.</p>
        </div>
        <div class="flex items-center gap-3">
            <x-ui.select wire:model.live="period" class="w-40">
                <option value="week">Cette semaine</option>
                <option value="month">Ce mois</option>
                <option value="quarter">Ce trimestre</option>
                <option value="year">Cette année</option>
            </x-ui.select>
            <a href="{{ route('productivity.projects.create') }}"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/80 flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Nouveau projet
            </a>
        </div>
    </div>

    <!-- My Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">Mes heures aujourd'hui</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($myTodayHours, 1) }}h</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">schedule</span>
                </div>
            </div>
        </div>

        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">Mes heures cette semaine</p>
                    <p class="text-2xl font-bold text-blue-400">{{ number_format($myWeekHours, 1) }}h</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-400">date_range</span>
                </div>
            </div>
        </div>

        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">Mes tâches en cours</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ $myTasks->count() }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-yellow-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-yellow-400">task</span>
                </div>
            </div>
        </div>

        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">Projets actifs</p>
                    <p class="text-2xl font-bold text-green-400">{{ $stats['projects']['active'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-green-400">folder</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Projects Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4 text-center">
                    <p class="text-3xl font-bold text-white">{{ $stats['projects']['total'] }}</p>
                    <p class="text-sm text-text-secondary">Projets</p>
                </div>
                <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4 text-center">
                    <p class="text-3xl font-bold text-green-400">{{ $stats['tasks']['completed'] }}</p>
                    <p class="text-sm text-text-secondary">Tâches terminées</p>
                </div>
                <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4 text-center">
                    <p class="text-3xl font-bold text-blue-400">{{ number_format($stats['time']['total_hours'], 0) }}h</p>
                    <p class="text-sm text-text-secondary">Heures totales</p>
                </div>
                <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4 text-center">
                    <p class="text-3xl font-bold text-primary">{{ number_format($stats['time']['billable_hours'], 0) }}h</p>
                    <p class="text-sm text-text-secondary">Heures facturables</p>
                </div>
            </div>

            <!-- Daily Hours Chart -->
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">bar_chart</span>
                    Heures par jour
                </h3>
                <div class="h-64" x-data="{
                    chart: null,
                    init() {
                        const ctx = this.$refs.canvas.getContext('2d');
                        this.chart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: {{ Js::from(collect($dailyHours)->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))) }},
                                datasets: [{
                                    label: 'Heures',
                                    data: {{ Js::from(collect($dailyHours)->pluck('hours')) }},
                                    backgroundColor: 'rgba(231, 111, 81, 0.6)',
                                    borderColor: 'rgba(231, 111, 81, 1)',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false }
                                },
                                scales: {
                                    x: {
                                        grid: { color: 'rgba(255,255,255,0.05)' },
                                        ticks: { color: 'rgba(255,255,255,0.5)' }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        grid: { color: 'rgba(255,255,255,0.05)' },
                                        ticks: { color: 'rgba(255,255,255,0.5)' }
                                    }
                                }
                            }
                        });
                    }
                }">
                    <canvas x-ref="canvas"></canvas>
                </div>
            </div>

            <!-- Time by Project -->
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">pie_chart</span>
                    Temps par projet
                </h3>
                @if(count($timeByProject) > 0)
                    <div class="space-y-3">
                        @php $maxHours = collect($timeByProject)->max('hours') ?: 1; @endphp
                        @foreach(array_slice($timeByProject, 0, 5) as $project)
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm text-white">{{ $project['project_name'] }}</span>
                                    <span class="text-sm text-text-secondary">{{ $project['hours'] }}h</span>
                                </div>
                                <div class="w-full h-2 bg-surface-highlight rounded-full overflow-hidden">
                                    <div class="h-full bg-primary rounded-full transition-all duration-500"
                                        style="width: {{ ($project['hours'] / $maxHours) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-text-secondary">
                        <p>Aucune donnée pour cette période</p>
                    </div>
                @endif
            </div>

            <!-- Budget Status -->
            @if(count($budgetStatus) > 0)
                <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">account_balance</span>
                        État des budgets
                    </h3>
                    <div class="space-y-3">
                        @foreach(array_slice($budgetStatus, 0, 5) as $project)
                            <div class="p-3 bg-surface-highlight rounded-lg">
                                <div class="flex justify-between items-center mb-2">
                                    <a href="{{ route('productivity.projects.show', $project['id']) }}"
                                        class="text-white hover:text-primary">{{ $project['name'] }}</a>
                                    <span class="text-sm {{ $project['status'] === 'over' ? 'text-red-400' : ($project['status'] === 'warning' ? 'text-yellow-400' : 'text-green-400') }}">
                                        {{ $project['usage'] }}%
                                    </span>
                                </div>
                                <div class="w-full h-2 bg-surface-dark rounded-full overflow-hidden">
                                    <div class="h-full rounded-full {{ $project['status'] === 'over' ? 'bg-red-500' : ($project['status'] === 'warning' ? 'bg-yellow-500' : 'bg-green-500') }}"
                                        style="width: {{ min($project['usage'], 100) }}%"></div>
                                </div>
                                <div class="flex justify-between text-xs text-text-secondary mt-1">
                                    <span>{{ number_format($project['cost'], 0, ',', ' ') }} FCFA</span>
                                    <span>{{ number_format($project['budget'], 0, ',', ' ') }} FCFA</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- My Tasks -->
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">task</span>
                        Mes tâches
                    </h3>
                    <a href="{{ route('productivity.time-tracker') }}" class="text-sm text-primary hover:text-primary/80">
                        Voir tout
                    </a>
                </div>

                @if($myTasks->count() > 0)
                    <div class="space-y-2">
                        @foreach($myTasks as $task)
                            @php
                                $statusColors = [
                                    'todo' => 'text-blue-400',
                                    'in_progress' => 'text-yellow-400',
                                    'review' => 'text-purple-400',
                                ];
                            @endphp
                            <a href="{{ route('productivity.tasks.show', $task) }}"
                                class="flex items-center gap-3 p-2 rounded-lg hover:bg-surface-highlight transition-colors">
                                <span class="material-symbols-outlined {{ $statusColors[$task->status] ?? 'text-gray-400' }}">
                                    {{ $task->status === 'in_progress' ? 'pending' : 'radio_button_unchecked' }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-white truncate">{{ $task->title }}</p>
                                    <p class="text-xs text-text-secondary">{{ $task->project->name }}</p>
                                </div>
                                @if($task->due_date)
                                    <span class="text-xs {{ $task->is_overdue ? 'text-red-400' : 'text-text-secondary' }}">
                                        {{ $task->due_date->format('d/m') }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-text-secondary text-sm">
                        <p>Aucune tâche en cours</p>
                    </div>
                @endif
            </div>

            <!-- Upcoming Deadlines -->
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">event</span>
                    Échéances à venir
                </h3>

                @if(count($upcomingDeadlines) > 0)
                    <div class="space-y-2">
                        @foreach(array_slice($upcomingDeadlines, 0, 5) as $deadline)
                            <div class="p-2 rounded-lg bg-surface-highlight">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ route('productivity.tasks.show', $deadline['id']) }}"
                                            class="text-sm text-white hover:text-primary truncate block">
                                            {{ $deadline['title'] }}
                                        </a>
                                        <p class="text-xs text-text-secondary">{{ $deadline['project_name'] }}</p>
                                    </div>
                                    <span class="text-xs px-2 py-1 rounded-full {{ $deadline['days_left'] <= 1 ? 'bg-red-500/20 text-red-400' : ($deadline['days_left'] <= 3 ? 'bg-yellow-500/20 text-yellow-400' : 'bg-blue-500/20 text-blue-400') }}">
                                        @if($deadline['days_left'] === 0)
                                            Aujourd'hui
                                        @elseif($deadline['days_left'] === 1)
                                            Demain
                                        @else
                                            {{ $deadline['days_left'] }}j
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-text-secondary text-sm">
                        <p>Aucune échéance proche</p>
                    </div>
                @endif
            </div>

            <!-- Recent Projects -->
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">folder</span>
                        Projets récents
                    </h3>
                    <a href="{{ route('productivity.projects.index') }}" class="text-sm text-primary hover:text-primary/80">
                        Voir tout
                    </a>
                </div>

                @if($recentProjects->count() > 0)
                    <div class="space-y-2">
                        @foreach($recentProjects as $project)
                            <a href="{{ route('productivity.projects.show', $project) }}"
                                class="block p-2 rounded-lg hover:bg-surface-highlight transition-colors">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-white">{{ $project->name }}</span>
                                    <span class="text-xs text-text-secondary">{{ $project->tasks_progress }}%</span>
                                </div>
                                <div class="w-full h-1 bg-surface-dark rounded-full mt-1 overflow-hidden">
                                    <div class="h-full bg-primary rounded-full" style="width: {{ $project->tasks_progress }}%"></div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-text-secondary text-sm">
                        <p>Aucun projet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
