<div class="flex flex-col h-full overflow-hidden">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-6 border-b border-[#3a2e24] bg-background-dark gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Tableau des Taches</h1>
            <p class="text-text-secondary text-sm">Gerez les taches par glisser-deposer.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <!-- Filters -->
            <select wire:model.live="employeeId" class="px-3 py-2 bg-surface-dark border border-[#3a2e24] rounded-xl text-white text-sm focus:outline-none focus:border-primary">
                <option value="">Tous les employes</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                @endforeach
            </select>
            <select wire:model.live="priority" class="px-3 py-2 bg-surface-dark border border-[#3a2e24] rounded-xl text-white text-sm focus:outline-none focus:border-primary">
                <option value="">Toutes priorites</option>
                @foreach($priorities as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            <!-- Stats -->
            <div class="flex gap-2">
                <div class="px-3 py-2 bg-surface-dark rounded-xl border border-[#3a2e24]">
                    <span class="text-text-secondary text-xs">Total:</span>
                    <span class="text-white font-bold ml-1">{{ $stats['total'] }}</span>
                </div>
                @if($stats['overdue'] > 0)
                    <div class="px-3 py-2 bg-red-500/10 rounded-xl border border-red-500/20">
                        <span class="text-red-400 text-xs">En retard:</span>
                        <span class="text-red-400 font-bold ml-1">{{ $stats['overdue'] }}</span>
                    </div>
                @endif
            </div>

            <a href="{{ route('hr.tasks.index') }}" class="bg-surface-dark hover:bg-surface-highlight text-text-secondary hover:text-white font-medium py-2 px-4 rounded-xl flex items-center gap-2 transition-colors border border-[#3a2e24]">
                <span class="material-symbols-outlined text-[20px]">list</span>
                Vue Liste
            </a>
            <a href="{{ route('hr.tasks.create') }}" class="bg-primary hover:bg-primary/90 text-white font-bold py-2 px-4 rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Nouvelle
            </a>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="flex-1 overflow-x-auto overflow-y-hidden p-6">
        <div class="flex h-full gap-6 min-w-[max-content]">
            @foreach ($statuses as $status)
                <div class="w-80 flex flex-col h-full bg-surface-dark rounded-2xl border {{ $status->border_color_class }} flex-shrink-0">
                    <!-- Column Header -->
                    <div class="p-4 border-b border-[#3a2e24] flex justify-between items-center sticky top-0 bg-surface-dark rounded-t-2xl z-10">
                        <div class="flex items-center gap-2">
                            <div class="size-3 rounded-full bg-{{ $status->color }}-500"></div>
                            <h3 class="font-bold text-white text-sm">{{ $status->name }}</h3>
                        </div>
                        <span class="text-xs font-bold text-text-secondary bg-background-dark px-2 py-1 rounded-lg">
                            {{ $this->getTasksForStatus($status->id)->count() }}
                        </span>
                    </div>

                    <!-- Draggable Area -->
                    <div class="flex-1 overflow-y-auto p-3 space-y-3 custom-scrollbar kanban-column" data-status-id="{{ $status->id }}">
                        @foreach ($this->getTasksForStatus($status->id) as $task)
                            <div data-id="{{ $task->id }}"
                                class="bg-background-dark p-4 rounded-xl border border-[#3a2e24] hover:border-primary/50 cursor-grab active:cursor-grabbing shadow-sm group transition-all task-card relative {{ $task->is_overdue ? 'border-red-500/50' : '' }}">

                                <!-- Priority indicator -->
                                <div class="absolute top-0 left-4 w-8 h-1 rounded-b-full bg-{{ $task->priority_color }}-500"></div>

                                <div class="flex justify-between items-start mb-2 mt-1">
                                    <a href="{{ route('hr.tasks.show', $task) }}" class="text-white font-bold text-sm leading-tight group-hover:text-primary transition-colors line-clamp-2 flex-1 pr-2">
                                        {{ $task->title }}
                                    </a>
                                    <!-- Action buttons -->
                                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('hr.tasks.edit', $task) }}"
                                            class="size-6 rounded-lg bg-surface-highlight hover:bg-primary/20 flex items-center justify-center text-text-secondary hover:text-primary transition-colors"
                                            title="Modifier">
                                            <span class="material-symbols-outlined text-[14px]">edit</span>
                                        </a>
                                        <button wire:click="confirmDelete({{ $task->id }})"
                                            class="size-6 rounded-lg bg-surface-highlight hover:bg-red-500/20 flex items-center justify-center text-text-secondary hover:text-red-400 transition-colors"
                                            title="Supprimer">
                                            <span class="material-symbols-outlined text-[14px]">delete</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Employee -->
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="size-5 rounded-full bg-cover bg-center"
                                        style='background-image: url("https://ui-avatars.com/api/?name={{ urlencode($task->employee->full_name) }}&background=random&color=fff&size=32");'>
                                    </div>
                                    <span class="text-text-secondary text-xs">{{ $task->employee->full_name }}</span>
                                </div>

                                <!-- Footer -->
                                <div class="flex justify-between items-center">
                                    <span class="text-xs px-2 py-0.5 rounded {{ $task->priority_class }}">
                                        {{ $task->priority_label }}
                                    </span>
                                    @if($task->due_date)
                                        <span class="text-xs {{ $task->is_overdue ? 'text-red-400' : ($task->is_due_soon ? 'text-amber-400' : 'text-text-secondary') }} flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">schedule</span>
                                            {{ $task->due_date->format('d/m') }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Progress bar if estimated hours -->
                                @if($task->estimated_hours)
                                    <div class="mt-3 w-full bg-[#3a2e24] rounded-full h-1 overflow-hidden">
                                        <div class="h-full rounded-full bg-primary transition-all" style="width: {{ $task->progress_percent }}%"></div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Column Footer -->
                    <div class="p-3 border-t border-[#3a2e24] bg-surface-dark/50 text-center rounded-b-2xl">
                        <p class="text-xs text-text-secondary">
                            {{ $this->getTasksForStatus($status->id)->count() }} tache(s)
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Delete Modal -->
    @if($showDeleteModal && $taskToDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6 max-w-md w-full shadow-xl">
                <div class="flex items-center gap-3 mb-4">
                    <div class="size-12 rounded-full bg-red-500/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-red-400 text-2xl">warning</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Confirmer la suppression</h3>
                        <p class="text-text-secondary text-sm">Cette action est irreversible</p>
                    </div>
                </div>
                <p class="text-text-secondary mb-6">
                    Etes-vous sur de vouloir supprimer la tache <strong class="text-white">"{{ $taskToDelete->title }}"</strong> ?
                </p>
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete" class="px-4 py-2 rounded-xl border border-[#3a2e24] text-text-secondary hover:text-white hover:bg-surface-highlight font-medium transition-colors">
                        Annuler
                    </button>
                    <button wire:click="deleteTask" class="px-4 py-2 rounded-xl bg-red-500 text-white hover:bg-red-600 font-medium transition-colors">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@script
<script>
    document.addEventListener('livewire:navigated', initSortable);
    document.addEventListener('DOMContentLoaded', initSortable);

    function initSortable() {
        document.querySelectorAll('.kanban-column').forEach(column => {
            if (column.sortableInstance) return;

            column.sortableInstance = Sortable.create(column, {
                group: 'tasks',
                animation: 150,
                ghostClass: 'opacity-50',
                dragClass: 'dragging',
                chosenClass: 'chosen',
                onEnd: function(evt) {
                    const taskId = evt.item.getAttribute('data-id');
                    const newStatusId = evt.to.getAttribute('data-status-id');

                    if (taskId && newStatusId) {
                        @this.call('updateStatus', taskId, newStatusId);
                    }
                }
            });
        });
    }

    initSortable();
</script>
@endscript
