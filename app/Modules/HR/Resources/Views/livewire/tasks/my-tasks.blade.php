<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Mes Taches</h1>
            <p class="text-text-secondary text-sm">Gerez vos taches personnelles</p>
        </div>
        <div class="flex gap-3">
            <button wire:click="toggleView" class="bg-surface-dark hover:bg-surface-highlight text-text-secondary hover:text-white font-medium py-2 px-4 rounded-xl flex items-center gap-2 transition-colors border border-[#3a2e24]">
                <span class="material-symbols-outlined text-[20px]">{{ $view === 'list' ? 'view_kanban' : 'list' }}</span>
                {{ $view === 'list' ? 'Vue Kanban' : 'Vue Liste' }}
            </button>
            <button wire:click="openQuickAdd" class="bg-primary hover:bg-primary/90 text-white font-bold py-2 px-4 rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Nouvelle Tache
            </button>
        </div>
    </div>

    @if(!$this->employee)
        <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-6 text-center">
            <span class="material-symbols-outlined text-amber-400 text-4xl mb-2">warning</span>
            <p class="text-amber-400">Votre compte n'est pas associe a un profil employe.</p>
            <p class="text-text-secondary text-sm mt-1">Contactez l'administrateur pour configurer votre profil.</p>
        </div>
    @else
        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-surface-dark rounded-xl p-4 border border-[#3a2e24]">
                <p class="text-text-secondary text-xs uppercase font-bold">Total</p>
                <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-surface-dark rounded-xl p-4 border border-[#3a2e24]">
                <p class="text-text-secondary text-xs uppercase font-bold">En cours</p>
                <p class="text-2xl font-bold text-blue-400">{{ $stats['pending'] }}</p>
            </div>
            <div class="bg-surface-dark rounded-xl p-4 border border-[#3a2e24]">
                <p class="text-text-secondary text-xs uppercase font-bold">Terminees aujourd'hui</p>
                <p class="text-2xl font-bold text-green-400">{{ $stats['completed_today'] }}</p>
            </div>
            <div class="bg-surface-dark rounded-xl p-4 border border-[#3a2e24]">
                <p class="text-text-secondary text-xs uppercase font-bold">En retard</p>
                <p class="text-2xl font-bold text-red-400">{{ $stats['overdue'] }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-surface-dark rounded-xl p-4 mb-6 border border-[#3a2e24]">
            <div class="flex flex-wrap gap-4">
                <select wire:model.live="statusId" class="px-4 py-2 bg-background-dark border border-[#3a2e24] rounded-xl text-white focus:outline-none focus:border-primary">
                    <option value="">Tous les statuts</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="priority" class="px-4 py-2 bg-background-dark border border-[#3a2e24] rounded-xl text-white focus:outline-none focus:border-primary">
                    <option value="">Toutes priorites</option>
                    @foreach($priorities as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($view === 'list')
            <!-- List View -->
            <div class="bg-surface-dark rounded-xl border border-[#3a2e24] overflow-hidden">
                <div class="divide-y divide-[#3a2e24]">
                    @forelse($tasks as $task)
                        <div class="p-4 hover:bg-surface-highlight transition-colors {{ $task->is_overdue ? 'bg-red-500/5' : '' }}">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <a href="{{ route('hr.tasks.show', $task) }}" class="text-white font-medium hover:text-primary transition-colors">
                                            {{ $task->title }}
                                        </a>
                                        <span class="text-xs px-2 py-0.5 rounded {{ $task->priority_class }}">
                                            {{ $task->priority_label }}
                                        </span>
                                        @if($task->is_overdue)
                                            <span class="text-xs px-2 py-0.5 rounded bg-red-500/20 text-red-400">En retard</span>
                                        @endif
                                    </div>
                                    @if($task->description)
                                        <p class="text-text-secondary text-sm line-clamp-1">{{ $task->description }}</p>
                                    @endif
                                    <div class="flex items-center gap-4 mt-2 text-xs text-text-secondary">
                                        @if($task->due_date)
                                            <span class="flex items-center gap-1 {{ $task->is_overdue ? 'text-red-400' : '' }}">
                                                <span class="material-symbols-outlined text-[14px]">schedule</span>
                                                {{ $task->due_date->format('d/m/Y') }}
                                            </span>
                                        @endif
                                        @if($task->estimated_hours)
                                            <span class="flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[14px]">timer</span>
                                                {{ $task->actual_hours ?? 0 }}h / {{ $task->estimated_hours }}h
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <select wire:change="quickStatusUpdate({{ $task->id }}, $event.target.value)"
                                        class="text-xs px-2 py-1 rounded-lg border-0 {{ $task->status->color_class }} cursor-pointer">
                                        @foreach($statuses as $status)
                                            <option value="{{ $status->id }}" {{ $task->status_id == $status->id ? 'selected' : '' }}>
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <a href="{{ route('hr.tasks.show', $task) }}" class="p-2 rounded-lg hover:bg-background-dark text-text-secondary hover:text-white transition-colors">
                                        <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-text-secondary">
                            <span class="material-symbols-outlined text-4xl mb-2">task_alt</span>
                            <p>Aucune tache pour le moment.</p>
                            <button wire:click="openQuickAdd" class="mt-4 text-primary hover:text-primary/80 font-medium">
                                Creer votre premiere tache
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>

            @if($tasks instanceof \Illuminate\Pagination\LengthAwarePaginator && $tasks->hasPages())
                <div class="mt-4">
                    {{ $tasks->links() }}
                </div>
            @endif
        @else
            <!-- Board View -->
            <div class="overflow-x-auto pb-4">
                <div class="flex gap-4 min-w-[max-content]">
                    @foreach ($statuses as $status)
                        <div class="w-72 flex flex-col bg-surface-dark rounded-xl border {{ $status->border_color_class }} flex-shrink-0">
                            <!-- Column Header -->
                            <div class="p-3 border-b border-[#3a2e24] flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <div class="size-2.5 rounded-full bg-{{ $status->color }}-500"></div>
                                    <h3 class="font-bold text-white text-sm">{{ $status->name }}</h3>
                                </div>
                                <span class="text-xs font-bold text-text-secondary bg-background-dark px-2 py-0.5 rounded">
                                    {{ $this->getTasksForStatus($status->id)->count() }}
                                </span>
                            </div>

                            <!-- Tasks -->
                            <div class="flex-1 p-3 space-y-2 max-h-[60vh] overflow-y-auto custom-scrollbar">
                                @foreach ($this->getTasksForStatus($status->id) as $task)
                                    <div class="bg-background-dark p-3 rounded-lg border border-[#3a2e24] hover:border-primary/50 transition-colors {{ $task->is_overdue ? 'border-red-500/30' : '' }}">
                                        <a href="{{ route('hr.tasks.show', $task) }}" class="text-white text-sm font-medium hover:text-primary transition-colors line-clamp-2">
                                            {{ $task->title }}
                                        </a>
                                        <div class="flex justify-between items-center mt-2">
                                            <span class="text-xs px-1.5 py-0.5 rounded {{ $task->priority_class }}">
                                                {{ $task->priority_label }}
                                            </span>
                                            @if($task->due_date)
                                                <span class="text-xs {{ $task->is_overdue ? 'text-red-400' : 'text-text-secondary' }}">
                                                    {{ $task->due_date->format('d/m') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    <!-- Quick Add Modal -->
    @if($showQuickAddModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6 max-w-md w-full shadow-xl">
                <h3 class="text-lg font-bold text-white mb-4">Nouvelle Tache</h3>

                <form wire:submit="quickAdd" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-2">Titre *</label>
                        <input type="text" wire:model="quickTitle"
                            class="w-full px-4 py-2 bg-background-dark border border-[#3a2e24] rounded-xl text-white placeholder-text-secondary focus:outline-none focus:border-primary"
                            placeholder="Qu'avez-vous a faire ?" autofocus>
                        @error('quickTitle') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-secondary mb-2">Priorite</label>
                            <select wire:model="quickPriority" class="w-full px-4 py-2 bg-background-dark border border-[#3a2e24] rounded-xl text-white focus:outline-none focus:border-primary">
                                @foreach($priorities as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-secondary mb-2">Echeance</label>
                            <input type="date" wire:model="quickDueDate"
                                class="w-full px-4 py-2 bg-background-dark border border-[#3a2e24] rounded-xl text-white focus:outline-none focus:border-primary">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" wire:click="$set('showQuickAddModal', false)" class="px-4 py-2 rounded-xl border border-[#3a2e24] text-text-secondary hover:text-white hover:bg-surface-highlight font-medium transition-colors">
                            Annuler
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-primary hover:bg-primary/90 text-white font-bold transition-colors">
                            Ajouter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
