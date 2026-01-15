<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold text-white">Tâches du projet</h3>
            <p class="text-sm text-text-secondary">{{ $tasksStats['total'] }} tâches au total</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="openQuickTaskModal" class="px-4 py-2 bg-surface-highlight text-white rounded-lg hover:bg-surface-highlight/80 flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Nouvelle tâche
            </button>
            <a href="{{ route('productivity.projects.tasks', $project) }}"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/80 flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">view_kanban</span>
                Vue Kanban
            </a>
        </div>
    </div>

    <!-- Tasks List -->
    @php
        $statusConfig = [
            'todo' => ['label' => 'À faire', 'color' => 'blue', 'icon' => 'radio_button_unchecked'],
            'in_progress' => ['label' => 'En cours', 'color' => 'yellow', 'icon' => 'pending'],
            'review' => ['label' => 'En revue', 'color' => 'purple', 'icon' => 'rate_review'],
            'done' => ['label' => 'Terminé', 'color' => 'green', 'icon' => 'check_circle'],
        ];
        $priorityConfig = [
            'low' => ['label' => 'Basse', 'color' => 'text-gray-400'],
            'medium' => ['label' => 'Moyenne', 'color' => 'text-blue-400'],
            'high' => ['label' => 'Haute', 'color' => 'text-orange-400'],
            'urgent' => ['label' => 'Urgente', 'color' => 'text-red-400'],
        ];
    @endphp

    @foreach($statusConfig as $status => $config)
        @php
            $tasks = $project->tasks->where('status', $status)->whereNull('parent_id');
        @endphp
        @if($tasks->count() > 0)
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl overflow-hidden">
                <div class="px-4 py-3 bg-surface-highlight border-b border-[#3a2e24] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-{{ $config['color'] }}-400">{{ $config['icon'] }}</span>
                        <h4 class="font-medium text-white">{{ $config['label'] }}</h4>
                        <span class="px-2 py-0.5 text-xs rounded-full bg-{{ $config['color'] }}-500/20 text-{{ $config['color'] }}-400">
                            {{ $tasks->count() }}
                        </span>
                    </div>
                </div>

                <div class="divide-y divide-[#3a2e24]">
                    @foreach($tasks as $task)
                        <div class="p-4 hover:bg-surface-highlight transition-colors">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('productivity.tasks.show', $task) }}"
                                            class="font-medium text-white hover:text-primary transition-colors">
                                            {{ $task->title }}
                                        </a>
                                        <span class="{{ $priorityConfig[$task->priority]['color'] ?? 'text-gray-400' }}">
                                            <span class="material-symbols-outlined text-[16px]">flag</span>
                                        </span>
                                    </div>
                                    @if($task->description)
                                        <p class="text-sm text-text-secondary mt-1 line-clamp-1">{{ $task->description }}</p>
                                    @endif
                                    <div class="flex items-center gap-4 mt-2 text-xs text-text-secondary">
                                        @if($task->assignee)
                                            <span class="flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[14px]">person</span>
                                                {{ $task->assignee->full_name }}
                                            </span>
                                        @endif
                                        @if($task->due_date)
                                            <span class="flex items-center gap-1 {{ $task->is_overdue ? 'text-red-400' : '' }}">
                                                <span class="material-symbols-outlined text-[14px]">event</span>
                                                {{ $task->due_date->format('d/m/Y') }}
                                            </span>
                                        @endif
                                        @if($task->estimated_hours)
                                            <span class="flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[14px]">schedule</span>
                                                {{ number_format($task->actual_hours, 1) }}/{{ $task->estimated_hours }}h
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($task->children->count() > 0)
                                        <span class="text-xs text-text-secondary flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">subdirectory_arrow_right</span>
                                            {{ $task->children->count() }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach

    @if($tasksStats['total'] === 0)
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-12 text-center">
            <span class="material-symbols-outlined text-5xl text-text-secondary mb-4">task</span>
            <h3 class="text-lg font-medium text-white mb-2">Aucune tâche</h3>
            <p class="text-text-secondary mb-4">Créez votre première tâche pour ce projet.</p>
            <button wire:click="openQuickTaskModal" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/80 inline-flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Nouvelle tâche
            </button>
        </div>
    @endif
</div>
