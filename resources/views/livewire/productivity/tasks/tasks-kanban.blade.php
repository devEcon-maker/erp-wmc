<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div class="flex items-center gap-4">
            <a href="{{ route('productivity.projects.show', $project) }}"
                class="p-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-white">{{ $project->name }}</h2>
                <p class="text-text-secondary text-sm">Tableau Kanban des tâches</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button wire:click="openTaskModal('todo')" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/80 flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Nouvelle tâche
            </button>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="flex space-x-4 overflow-x-auto pb-4" x-data>
        @foreach($statuses as $status => $config)
            @php
                $statusTasks = $tasksByStatus[$status] ?? collect([]);
                $colorClasses = [
                    'blue' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
                    'yellow' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
                    'purple' => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
                    'green' => 'bg-green-500/20 text-green-400 border-green-500/30',
                ];
                $headerColor = $colorClasses[$config['color']] ?? 'bg-gray-500/20 text-gray-400';
            @endphp

            <div class="flex-shrink-0 w-80 bg-surface-dark border border-[#3a2e24] rounded-xl"
                x-on:drop="$wire.updateTaskStatus($event.dataTransfer.getData('taskId'), '{{ $status }}')"
                x-on:dragover.prevent>

                <!-- Column Header -->
                <div class="flex justify-between items-center p-4 border-b border-[#3a2e24]">
                    <h3 class="font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-{{ $config['color'] }}-400">{{ $config['icon'] }}</span>
                        {{ $config['label'] }}
                    </h3>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 text-xs rounded-full {{ $headerColor }}">
                            {{ count($statusTasks) }}
                        </span>
                        <button wire:click="openTaskModal('{{ $status }}')"
                            class="p-1 text-text-secondary hover:text-white hover:bg-surface-highlight rounded">
                            <span class="material-symbols-outlined text-[20px]">add</span>
                        </button>
                    </div>
                </div>

                <!-- Tasks -->
                <div class="p-3 space-y-3 min-h-[300px] max-h-[calc(100vh-300px)] overflow-y-auto custom-scrollbar">
                    @foreach($statusTasks as $task)
                        @php
                            $priorityColors = [
                                'low' => 'text-gray-400',
                                'medium' => 'text-blue-400',
                                'high' => 'text-orange-400',
                                'urgent' => 'text-red-400',
                            ];
                        @endphp
                        <div class="bg-surface-highlight border border-[#3a2e24] rounded-lg p-4 cursor-grab hover:border-primary/50 transition-all group"
                            draggable="true"
                            x-on:dragstart="$event.dataTransfer.setData('taskId', {{ $task->id }})">

                            <!-- Task Header -->
                            <div class="flex justify-between items-start gap-2">
                                <a href="{{ route('productivity.tasks.show', $task) }}"
                                    class="font-medium text-white hover:text-primary transition-colors flex-1">
                                    {{ $task->title }}
                                </a>
                                <span class="{{ $priorityColors[$task->priority] ?? 'text-gray-400' }}">
                                    <span class="material-symbols-outlined text-[18px]">flag</span>
                                </span>
                            </div>

                            <!-- Description -->
                            @if($task->description)
                                <p class="text-sm text-text-secondary mt-2 line-clamp-2">{{ $task->description }}</p>
                            @endif

                            <!-- Meta -->
                            <div class="flex flex-wrap items-center gap-2 mt-3 text-xs text-text-secondary">
                                @if($task->due_date)
                                    <span class="flex items-center gap-1 {{ $task->is_overdue ? 'text-red-400' : '' }}">
                                        <span class="material-symbols-outlined text-[14px]">event</span>
                                        {{ $task->due_date->format('d/m') }}
                                    </span>
                                @endif
                                @if($task->estimated_hours)
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">schedule</span>
                                        {{ number_format($task->actual_hours, 1) }}/{{ $task->estimated_hours }}h
                                    </span>
                                @endif
                                @if($task->children->count() > 0)
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">subdirectory_arrow_right</span>
                                        {{ $task->children->count() }}
                                    </span>
                                @endif
                            </div>

                            <!-- Footer -->
                            <div class="flex justify-between items-center mt-3 pt-3 border-t border-[#3a2e24]">
                                @if($task->assignee)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-primary/20 flex items-center justify-center text-xs font-medium text-primary">
                                            {{ substr($task->assignee->first_name, 0, 1) }}{{ substr($task->assignee->last_name, 0, 1) }}
                                        </div>
                                        <span class="text-xs text-text-secondary">{{ $task->assignee->first_name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-text-secondary">Non assigné</span>
                                @endif

                                <!-- Actions -->
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                                    <button wire:click="editTask({{ $task->id }})"
                                        class="p-1 text-text-secondary hover:text-white rounded">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                    </button>
                                    <button wire:click="duplicateTask({{ $task->id }})"
                                        class="p-1 text-text-secondary hover:text-white rounded">
                                        <span class="material-symbols-outlined text-[16px]">content_copy</span>
                                    </button>
                                    <button wire:click="deleteTask({{ $task->id }})"
                                        wire:confirm="Supprimer cette tâche ?"
                                        class="p-1 text-red-400 hover:text-red-300 rounded">
                                        <span class="material-symbols-outlined text-[16px]">delete</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if(count($statusTasks) === 0)
                        <div class="text-center py-8 text-text-secondary text-sm">
                            <span class="material-symbols-outlined text-3xl mb-2 opacity-50">inbox</span>
                            <p>Aucune tâche</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Tip -->
    <div class="text-sm text-text-secondary flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">info</span>
        Glissez-déposez les tâches pour changer leur statut.
    </div>

    <!-- Task Modal -->
    @if($showTaskModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="closeTaskModal">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-white mb-4">
                    {{ $editingTask ? 'Modifier la tâche' : 'Nouvelle tâche' }}
                </h3>

                <div class="space-y-4">
                    <x-ui.input
                        label="Titre *"
                        wire:model="taskForm.title"
                        placeholder="Titre de la tâche"
                        :error="$errors->first('taskForm.title')"
                    />

                    <x-ui.textarea
                        label="Description"
                        wire:model="taskForm.description"
                        placeholder="Description..."
                        rows="3"
                    />

                    <div class="grid grid-cols-2 gap-4">
                        <x-ui.select label="Assigné à" wire:model="taskForm.assigned_to">
                            <option value="">Non assigné</option>
                            @foreach($project->members as $member)
                                <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.select label="Priorité" wire:model="taskForm.priority">
                            <option value="low">Basse</option>
                            <option value="medium">Moyenne</option>
                            <option value="high">Haute</option>
                            <option value="urgent">Urgente</option>
                        </x-ui.select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <x-ui.input
                            type="date"
                            label="Date d'échéance"
                            wire:model="taskForm.due_date"
                        />

                        <x-ui.input
                            type="number"
                            step="0.5"
                            label="Heures estimées"
                            wire:model="taskForm.estimated_hours"
                            placeholder="0"
                        />
                    </div>

                    <x-ui.select label="Statut" wire:model="taskForm.status">
                        @foreach($statuses as $key => $config)
                            <option value="{{ $key }}">{{ $config['label'] }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <x-ui.button type="secondary" wire:click="closeTaskModal">Annuler</x-ui.button>
                    <x-ui.button wire:click="saveTask">
                        {{ $editingTask ? 'Mettre à jour' : 'Créer' }}
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif
</div>
