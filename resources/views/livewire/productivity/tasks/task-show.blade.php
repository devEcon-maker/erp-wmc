<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div class="flex items-start gap-4">
            <a href="{{ route('productivity.projects.tasks', $task->project) }}"
                class="p-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-sm text-text-secondary">
                        <a href="{{ route('productivity.projects.show', $task->project) }}" class="hover:text-primary">
                            {{ $task->project->name }}
                        </a>
                    </span>
                    @if($task->parent)
                        <span class="text-text-secondary">/</span>
                        <a href="{{ route('productivity.tasks.show', $task->parent) }}" class="text-sm text-text-secondary hover:text-primary">
                            {{ Str::limit($task->parent->title, 30) }}
                        </a>
                    @endif
                </div>
                <h2 class="text-2xl font-bold text-white">{{ $task->title }}</h2>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('productivity.projects.tasks', $task->project) }}"
                class="px-4 py-2 bg-surface-highlight text-white rounded-lg hover:bg-surface-highlight/80 flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">view_kanban</span>
                Kanban
            </a>
            <button wire:click="delete" wire:confirm="Supprimer cette tâche ?"
                class="px-4 py-2 bg-red-500/20 text-red-400 rounded-lg hover:bg-red-500/30 flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">delete</span>
                Supprimer
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">description</span>
                    Description
                </h3>
                @if($task->description)
                    <p class="text-text-secondary whitespace-pre-line">{{ $task->description }}</p>
                @else
                    <p class="text-text-secondary italic">Aucune description</p>
                @endif
            </div>

            <!-- Subtasks -->
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">subdirectory_arrow_right</span>
                        Sous-tâches
                    </h3>
                    <button wire:click="openSubtaskModal" class="text-sm text-primary hover:text-primary/80 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Ajouter
                    </button>
                </div>

                @if($task->children->count() > 0)
                    <div class="space-y-2">
                        @foreach($task->children as $subtask)
                            @php
                                $statusColors = [
                                    'todo' => 'text-blue-400',
                                    'in_progress' => 'text-yellow-400',
                                    'review' => 'text-purple-400',
                                    'done' => 'text-green-400',
                                ];
                                $statusIcons = [
                                    'todo' => 'radio_button_unchecked',
                                    'in_progress' => 'pending',
                                    'review' => 'rate_review',
                                    'done' => 'check_circle',
                                ];
                            @endphp
                            <a href="{{ route('productivity.tasks.show', $subtask) }}"
                                class="flex items-center justify-between p-3 bg-surface-highlight rounded-lg hover:bg-surface-highlight/80 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined {{ $statusColors[$subtask->status] ?? '' }}">
                                        {{ $statusIcons[$subtask->status] ?? 'radio_button_unchecked' }}
                                    </span>
                                    <span class="text-white">{{ $subtask->title }}</span>
                                </div>
                                @if($subtask->assignee)
                                    <div class="w-6 h-6 rounded-full bg-primary/20 flex items-center justify-center text-xs font-medium text-primary">
                                        {{ substr($subtask->assignee->first_name, 0, 1) }}{{ substr($subtask->assignee->last_name, 0, 1) }}
                                    </div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-text-secondary">
                        <p>Aucune sous-tâche</p>
                    </div>
                @endif
            </div>

            <!-- Time Entries -->
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">schedule</span>
                        Temps passé
                        <span class="text-sm font-normal text-text-secondary">
                            ({{ number_format($task->actual_hours, 1) }}h
                            @if($task->estimated_hours)
                                / {{ $task->estimated_hours }}h
                            @endif
                            )
                        </span>
                    </h3>
                    <button wire:click="openTimeModal" class="text-sm text-primary hover:text-primary/80 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Ajouter
                    </button>
                </div>

                @if($task->timeEntries->count() > 0)
                    <div class="space-y-2">
                        @foreach($task->timeEntries->sortByDesc('date') as $entry)
                            <div class="flex items-center justify-between p-3 bg-surface-highlight rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-xs font-medium text-primary">
                                        {{ substr($entry->employee->first_name ?? '', 0, 1) }}{{ substr($entry->employee->last_name ?? '', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm text-white">{{ $entry->employee->full_name ?? '-' }}</p>
                                        <p class="text-xs text-text-secondary">
                                            {{ $entry->date->format('d/m/Y') }}
                                            @if($entry->description)
                                                · {{ Str::limit($entry->description, 40) }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-white font-medium">{{ number_format($entry->hours, 1) }}h</span>
                                    @if($entry->billable)
                                        <span class="text-xs text-green-400">Facturable</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-text-secondary">
                        <p>Aucun temps enregistré</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status -->
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <h4 class="text-sm font-medium text-text-secondary uppercase tracking-wide mb-3">Statut</h4>
                <div class="grid grid-cols-2 gap-2">
                    @php
                        $statusConfig = [
                            'todo' => ['label' => 'À faire', 'color' => 'blue', 'icon' => 'radio_button_unchecked'],
                            'in_progress' => ['label' => 'En cours', 'color' => 'yellow', 'icon' => 'pending'],
                            'review' => ['label' => 'Revue', 'color' => 'purple', 'icon' => 'rate_review'],
                            'done' => ['label' => 'Terminé', 'color' => 'green', 'icon' => 'check_circle'],
                        ];
                    @endphp
                    @foreach($statusConfig as $status => $config)
                        <button wire:click="updateStatus('{{ $status }}')"
                            class="p-2 rounded-lg text-sm flex items-center gap-2 transition-colors
                                {{ $task->status === $status
                                    ? 'bg-'.$config['color'].'-500/20 text-'.$config['color'].'-400 border border-'.$config['color'].'-500/30'
                                    : 'bg-surface-highlight text-text-secondary hover:text-white' }}">
                            <span class="material-symbols-outlined text-[18px]">{{ $config['icon'] }}</span>
                            {{ $config['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Priority -->
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <h4 class="text-sm font-medium text-text-secondary uppercase tracking-wide mb-3">Priorité</h4>
                <div class="grid grid-cols-2 gap-2">
                    @php
                        $priorityConfig = [
                            'low' => ['label' => 'Basse', 'color' => 'gray'],
                            'medium' => ['label' => 'Moyenne', 'color' => 'blue'],
                            'high' => ['label' => 'Haute', 'color' => 'orange'],
                            'urgent' => ['label' => 'Urgente', 'color' => 'red'],
                        ];
                    @endphp
                    @foreach($priorityConfig as $priority => $config)
                        <button wire:click="updatePriority('{{ $priority }}')"
                            class="p-2 rounded-lg text-sm flex items-center gap-2 transition-colors
                                {{ $task->priority === $priority
                                    ? 'bg-'.$config['color'].'-500/20 text-'.$config['color'].'-400 border border-'.$config['color'].'-500/30'
                                    : 'bg-surface-highlight text-text-secondary hover:text-white' }}">
                            <span class="material-symbols-outlined text-[18px]">flag</span>
                            {{ $config['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Assignee -->
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <h4 class="text-sm font-medium text-text-secondary uppercase tracking-wide mb-3">Assigné à</h4>
                <select wire:change="assignTo($event.target.value)"
                    class="w-full bg-surface-highlight border border-[#3a2e24] text-white rounded-lg px-3 py-2">
                    <option value="">Non assigné</option>
                    @foreach($task->project->members as $member)
                        <option value="{{ $member->id }}" {{ $task->assigned_to == $member->id ? 'selected' : '' }}>
                            {{ $member->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Details -->
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <h4 class="text-sm font-medium text-text-secondary uppercase tracking-wide mb-3">Détails</h4>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-text-secondary">Date d'échéance</p>
                        <p class="text-white {{ $task->is_overdue ? 'text-red-400' : '' }}">
                            {{ $task->due_date?->format('d/m/Y') ?? '-' }}
                            @if($task->is_overdue)
                                <span class="text-xs">(en retard)</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-text-secondary">Heures estimées</p>
                        <p class="text-white">{{ $task->estimated_hours ?? '-' }}h</p>
                    </div>
                    <div>
                        <p class="text-xs text-text-secondary">Heures réelles</p>
                        <p class="text-white">{{ number_format($task->actual_hours, 1) }}h</p>
                    </div>
                    @if($task->estimated_hours && $task->actual_hours > 0)
                        <div>
                            <p class="text-xs text-text-secondary">Écart</p>
                            @php
                                $variance = $task->hours_variance;
                            @endphp
                            <p class="{{ $variance < 0 ? 'text-red-400' : 'text-green-400' }}">
                                {{ $variance >= 0 ? '+' : '' }}{{ number_format($variance, 1) }}h
                            </p>
                        </div>
                    @endif
                    <div>
                        <p class="text-xs text-text-secondary">Créée le</p>
                        <p class="text-white">{{ $task->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Modal -->
    @if($showTimeModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="closeTimeModal">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Ajouter du temps</h3>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <x-ui.input
                            type="number"
                            step="0.25"
                            label="Heures *"
                            wire:model="timeForm.hours"
                            placeholder="0"
                            :error="$errors->first('timeForm.hours')"
                        />
                        <x-ui.input
                            type="date"
                            label="Date *"
                            wire:model="timeForm.date"
                            :error="$errors->first('timeForm.date')"
                        />
                    </div>

                    <x-ui.textarea
                        label="Description"
                        wire:model="timeForm.description"
                        placeholder="Description du travail effectué..."
                        rows="2"
                    />

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="timeForm.billable"
                            class="w-4 h-4 rounded bg-surface-highlight border-[#3a2e24] text-primary focus:ring-primary">
                        <span class="text-white">Facturable</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <x-ui.button type="secondary" wire:click="closeTimeModal">Annuler</x-ui.button>
                    <x-ui.button wire:click="addTimeEntry">Ajouter</x-ui.button>
                </div>
            </div>
        </div>
    @endif

    <!-- Subtask Modal -->
    @if($showSubtaskModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="closeSubtaskModal">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Nouvelle sous-tâche</h3>

                <div class="space-y-4">
                    <x-ui.input
                        label="Titre *"
                        wire:model="subtaskForm.title"
                        placeholder="Titre de la sous-tâche"
                        :error="$errors->first('subtaskForm.title')"
                    />

                    <x-ui.select label="Assigné à" wire:model="subtaskForm.assigned_to">
                        <option value="">Non assigné</option>
                        @foreach($task->project->members as $member)
                            <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                        @endforeach
                    </x-ui.select>

                    <div class="grid grid-cols-2 gap-4">
                        <x-ui.select label="Priorité" wire:model="subtaskForm.priority">
                            <option value="low">Basse</option>
                            <option value="medium">Moyenne</option>
                            <option value="high">Haute</option>
                            <option value="urgent">Urgente</option>
                        </x-ui.select>

                        <x-ui.input
                            type="date"
                            label="Échéance"
                            wire:model="subtaskForm.due_date"
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <x-ui.button type="secondary" wire:click="closeSubtaskModal">Annuler</x-ui.button>
                    <x-ui.button wire:click="createSubtask">Créer</x-ui.button>
                </div>
            </div>
        </div>
    @endif
</div>
