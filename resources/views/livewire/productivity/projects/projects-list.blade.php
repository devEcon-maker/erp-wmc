<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white">Projets</h2>
            <p class="text-text-secondary text-sm">Gérez vos projets et suivez leur avancement.</p>
        </div>
        <x-ui.button href="{{ route('productivity.projects.create') }}" class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Nouveau Projet
        </x-ui.button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">Total Projets</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">folder</span>
                </div>
            </div>
        </div>

        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">Actifs</p>
                    <p class="text-2xl font-bold text-green-400">{{ $stats['active'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-green-400">play_circle</span>
                </div>
            </div>
        </div>

        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">En Planification</p>
                    <p class="text-2xl font-bold text-blue-400">{{ $stats['planning'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-400">edit_calendar</span>
                </div>
            </div>
        </div>

        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">Terminés</p>
                    <p class="text-2xl font-bold text-purple-400">{{ $stats['completed'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-purple-400">check_circle</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <x-ui.input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher un projet..."
                icon="search"
            />

            <x-ui.select wire:model.live="status">
                <option value="">Tous les statuts</option>
                <option value="planning">Planification</option>
                <option value="active">Actif</option>
                <option value="on_hold">En pause</option>
                <option value="completed">Terminé</option>
                <option value="cancelled">Annulé</option>
            </x-ui.select>

            <x-ui.select wire:model.live="managerId">
                <option value="">Tous les managers</option>
                @foreach($managers as $manager)
                    <option value="{{ $manager->id }}">{{ $manager->full_name }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select wire:model.live="contactId">
                <option value="">Tous les clients</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select wire:model.live="billingType">
                <option value="">Tous les types</option>
                <option value="fixed">Forfait</option>
                <option value="hourly">Horaire</option>
                <option value="non_billable">Non facturable</option>
            </x-ui.select>
        </div>
    </div>

    <!-- Projects Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($projects as $project)
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl overflow-hidden hover:border-primary/50 transition-colors">
                <!-- Project Header -->
                <div class="p-4 border-b border-[#3a2e24]">
                    <div class="flex justify-between items-start">
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('productivity.projects.show', $project) }}"
                                class="text-lg font-semibold text-white hover:text-primary transition-colors block truncate">
                                {{ $project->name }}
                            </a>
                            @if($project->contact)
                                <p class="text-sm text-text-secondary truncate">
                                    <span class="material-symbols-outlined text-[14px] align-middle mr-1">business</span>
                                    {{ $project->contact->company_name }}
                                </p>
                            @endif
                        </div>
                        @php
                            $statusColors = [
                                'planning' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
                                'active' => 'bg-green-500/20 text-green-400 border-green-500/30',
                                'on_hold' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
                                'completed' => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
                                'cancelled' => 'bg-red-500/20 text-red-400 border-red-500/30',
                            ];
                            $statusLabels = [
                                'planning' => 'Planification',
                                'active' => 'Actif',
                                'on_hold' => 'En pause',
                                'completed' => 'Terminé',
                                'cancelled' => 'Annulé',
                            ];
                        @endphp
                        <span class="px-2 py-1 text-xs rounded-full border {{ $statusColors[$project->status] ?? '' }}">
                            {{ $statusLabels[$project->status] ?? $project->status }}
                        </span>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="px-4 py-3 bg-surface-highlight">
                    <div class="flex justify-between items-center text-xs text-text-secondary mb-1">
                        <span>Progression</span>
                        <span>{{ $project->tasks_progress }}%</span>
                    </div>
                    <div class="w-full h-2 bg-surface-dark rounded-full overflow-hidden">
                        <div class="h-full bg-primary rounded-full transition-all duration-300"
                            style="width: {{ $project->tasks_progress }}%"></div>
                    </div>
                </div>

                <!-- Project Info -->
                <div class="p-4 space-y-3">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-text-secondary text-xs">Manager</p>
                            <p class="text-white">{{ $project->manager?->full_name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-text-secondary text-xs">Type</p>
                            @php
                                $billingLabels = [
                                    'fixed' => 'Forfait',
                                    'hourly' => 'Horaire',
                                    'non_billable' => 'Non facturable',
                                ];
                            @endphp
                            <p class="text-white">{{ $billingLabels[$project->billing_type] ?? $project->billing_type }}</p>
                        </div>
                        <div>
                            <p class="text-text-secondary text-xs">Début</p>
                            <p class="text-white">{{ $project->start_date?->format('d/m/Y') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-text-secondary text-xs">Fin</p>
                            <p class="text-white">{{ $project->end_date?->format('d/m/Y') ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="flex justify-between items-center pt-3 border-t border-[#3a2e24]">
                        <div class="flex items-center gap-4 text-sm text-text-secondary">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-[18px]">task</span>
                                {{ $project->tasks->count() }} tâches
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-[18px]">schedule</span>
                                {{ number_format($project->total_hours, 1) }}h
                            </span>
                        </div>
                        @if($project->budget)
                            <span class="text-sm font-medium text-primary">
                                {{ number_format($project->budget, 0, ',', ' ') }} FCFA
                            </span>
                        @endif
                    </div>

                    <!-- Members -->
                    @if($project->members->count() > 0)
                        <div class="flex items-center gap-2 pt-3 border-t border-[#3a2e24]">
                            <div class="flex -space-x-2">
                                @foreach($project->members->take(4) as $member)
                                    <div class="w-8 h-8 rounded-full bg-primary/20 border-2 border-surface-dark flex items-center justify-center text-xs font-medium text-primary"
                                        title="{{ $member->full_name }}">
                                        {{ substr($member->first_name, 0, 1) }}{{ substr($member->last_name, 0, 1) }}
                                    </div>
                                @endforeach
                                @if($project->members->count() > 4)
                                    <div class="w-8 h-8 rounded-full bg-surface-highlight border-2 border-surface-dark flex items-center justify-center text-xs font-medium text-text-secondary">
                                        +{{ $project->members->count() - 4 }}
                                    </div>
                                @endif
                            </div>
                            <span class="text-xs text-text-secondary">{{ $project->members->count() }} membres</span>
                        </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="px-4 py-3 bg-surface-highlight border-t border-[#3a2e24] flex justify-between">
                    <a href="{{ route('productivity.projects.show', $project) }}"
                        class="text-sm text-primary hover:text-primary/80 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                        Voir
                    </a>
                    <a href="{{ route('productivity.projects.edit', $project) }}"
                        class="text-sm text-text-secondary hover:text-white flex items-center gap-1">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                        Modifier
                    </a>
                    <a href="{{ route('productivity.projects.tasks', $project) }}"
                        class="text-sm text-text-secondary hover:text-white flex items-center gap-1">
                        <span class="material-symbols-outlined text-[18px]">view_kanban</span>
                        Kanban
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-surface-dark border border-[#3a2e24] rounded-xl p-12 text-center">
                <span class="material-symbols-outlined text-5xl text-text-secondary mb-4">folder_off</span>
                <h3 class="text-lg font-medium text-white mb-2">Aucun projet trouvé</h3>
                <p class="text-text-secondary mb-4">Créez votre premier projet pour commencer.</p>
                <x-ui.button href="{{ route('productivity.projects.create') }}" class="inline-flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    Nouveau Projet
                </x-ui.button>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($projects->hasPages())
        <div class="mt-6">
            {{ $projects->links() }}
        </div>
    @endif
</div>
