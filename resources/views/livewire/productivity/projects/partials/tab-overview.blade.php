<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Description -->
        @if($project->description)
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">description</span>
                    Description
                </h3>
                <p class="text-text-secondary whitespace-pre-line">{{ $project->description }}</p>
            </div>
        @endif

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4 text-center">
                <p class="text-3xl font-bold text-white">{{ $tasksStats['total'] }}</p>
                <p class="text-sm text-text-secondary">Tâches</p>
            </div>
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4 text-center">
                <p class="text-3xl font-bold text-green-400">{{ $tasksStats['done'] }}</p>
                <p class="text-sm text-text-secondary">Terminées</p>
            </div>
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4 text-center">
                <p class="text-3xl font-bold text-blue-400">{{ number_format($timeStats['total_hours'], 1) }}</p>
                <p class="text-sm text-text-secondary">Heures</p>
            </div>
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4 text-center">
                <p class="text-3xl font-bold text-primary">{{ $project->members->count() }}</p>
                <p class="text-sm text-text-secondary">Membres</p>
            </div>
        </div>

        <!-- Tasks by Status -->
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">task</span>
                    Tâches par statut
                </h3>
                <button wire:click="openQuickTaskModal" class="text-sm text-primary hover:text-primary/80 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Ajouter
                </button>
            </div>

            <div class="grid grid-cols-4 gap-3">
                @php
                    $statusConfig = [
                        'todo' => ['label' => 'À faire', 'color' => 'blue'],
                        'in_progress' => ['label' => 'En cours', 'color' => 'yellow'],
                        'review' => ['label' => 'Revue', 'color' => 'purple'],
                        'done' => ['label' => 'Terminé', 'color' => 'green'],
                    ];
                @endphp
                @foreach($statusConfig as $status => $config)
                    <div class="bg-surface-highlight rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-{{ $config['color'] }}-400">{{ $tasksStats[$status] ?? 0 }}</p>
                        <p class="text-xs text-text-secondary">{{ $config['label'] }}</p>
                    </div>
                @endforeach
            </div>

            @if($tasksStats['overdue'] > 0)
                <div class="mt-4 p-3 bg-red-500/10 border border-red-500/30 rounded-lg flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-400">warning</span>
                    <span class="text-sm text-red-400">{{ $tasksStats['overdue'] }} tâche(s) en retard</span>
                </div>
            @endif
        </div>

        <!-- Recent Activity -->
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">history</span>
                Temps récent
            </h3>

            @if($recentTimeEntries->count() > 0)
                <div class="space-y-3">
                    @foreach($recentTimeEntries as $entry)
                        <div class="flex items-center justify-between p-3 bg-surface-highlight rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-sm font-medium text-primary">
                                    {{ substr($entry->employee->first_name ?? '', 0, 1) }}{{ substr($entry->employee->last_name ?? '', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-white">{{ $entry->employee->full_name ?? '-' }}</p>
                                    <p class="text-sm text-text-secondary">
                                        {{ $entry->task?->title ?? 'Sans tâche' }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-white font-medium">{{ number_format($entry->hours, 1) }}h</p>
                                <p class="text-xs text-text-secondary">{{ $entry->date->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-text-secondary">
                    <span class="material-symbols-outlined text-3xl mb-2 opacity-50">schedule</span>
                    <p>Aucune entrée de temps</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Project Info -->
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">info</span>
                Informations
            </h3>

            <div class="space-y-4">
                <div>
                    <p class="text-xs text-text-secondary uppercase tracking-wide">Manager</p>
                    <div class="flex items-center gap-2 mt-1">
                        <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-xs font-medium text-primary">
                            {{ substr($project->manager->first_name ?? '', 0, 1) }}{{ substr($project->manager->last_name ?? '', 0, 1) }}
                        </div>
                        <span class="text-white">{{ $project->manager?->full_name ?? '-' }}</span>
                    </div>
                </div>

                @if($project->contact)
                    <div>
                        <p class="text-xs text-text-secondary uppercase tracking-wide">Client</p>
                        <a href="{{ route('crm.contacts.show', $project->contact) }}"
                            class="text-white hover:text-primary flex items-center gap-1 mt-1">
                            <span class="material-symbols-outlined text-[18px]">business</span>
                            {{ $project->contact->company_name }}
                        </a>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-text-secondary uppercase tracking-wide">Début</p>
                        <p class="text-white mt-1">{{ $project->start_date?->format('d/m/Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-text-secondary uppercase tracking-wide">Fin</p>
                        <p class="text-white mt-1">{{ $project->end_date?->format('d/m/Y') ?? '-' }}</p>
                    </div>
                </div>

                @if($project->budget)
                    <div>
                        <p class="text-xs text-text-secondary uppercase tracking-wide">Budget</p>
                        <p class="text-xl font-bold text-primary mt-1">{{ number_format($project->budget, 0, ',', ' ') }} FCFA</p>
                    </div>
                @endif

                @if($project->billing_type === 'hourly' && $project->hourly_rate)
                    <div>
                        <p class="text-xs text-text-secondary uppercase tracking-wide">Taux horaire</p>
                        <p class="text-white mt-1">{{ number_format($project->hourly_rate, 2) }} FCFA/h</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Team -->
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">group</span>
                    Équipe
                </h3>
                <button wire:click="openMemberModal" class="text-primary hover:text-primary/80">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                </button>
            </div>

            @if($project->members->count() > 0)
                <div class="space-y-2">
                    @foreach($project->members->take(5) as $member)
                        <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-surface-highlight">
                            <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-xs font-medium text-primary">
                                {{ substr($member->first_name, 0, 1) }}{{ substr($member->last_name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-white truncate">{{ $member->full_name }}</p>
                                @if($member->pivot->role)
                                    <p class="text-xs text-text-secondary">{{ $member->pivot->role }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @if($project->members->count() > 5)
                        <button wire:click="setTab('team')" class="text-sm text-primary hover:text-primary/80 w-full text-center py-2">
                            Voir tous ({{ $project->members->count() }})
                        </button>
                    @endif
                </div>
            @else
                <div class="text-center py-4 text-text-secondary text-sm">
                    <p>Aucun membre</p>
                </div>
            @endif
        </div>
    </div>
</div>
