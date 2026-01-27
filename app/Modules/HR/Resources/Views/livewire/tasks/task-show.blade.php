<div class="p-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <a href="{{ route('hr.tasks.index') }}" class="text-text-secondary hover:text-white text-sm flex items-center gap-1 mb-2 transition-colors">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Retour aux taches
            </a>
            <h1 class="text-2xl font-bold text-white">{{ $task->title }}</h1>
            <div class="flex items-center gap-3 mt-2">
                <span class="text-xs px-3 py-1 rounded-lg {{ $task->status->color_class }}">
                    {{ $task->status->name }}
                </span>
                <span class="text-xs px-3 py-1 rounded-lg {{ $task->priority_class }}">
                    {{ $task->priority_label }}
                </span>
                @if($task->is_overdue)
                    <span class="text-xs px-3 py-1 rounded-lg bg-red-500/20 text-red-400">En retard</span>
                @endif
            </div>
        </div>
        <div class="flex gap-2">
            @if(!$task->status->is_completed)
                <button wire:click="markAsCompleted" class="bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-xl flex items-center gap-2 transition-colors">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                    Terminer
                </button>
            @endif
            <a href="{{ route('hr.tasks.edit', $task) }}" class="bg-surface-dark hover:bg-surface-highlight text-white font-medium py-2 px-4 rounded-xl flex items-center gap-2 transition-colors border border-[#3a2e24]">
                <span class="material-symbols-outlined text-[20px]">edit</span>
                Modifier
            </a>
            <button wire:click="confirmDelete" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 font-medium py-2 px-4 rounded-xl flex items-center gap-2 transition-colors border border-red-500/20">
                <span class="material-symbols-outlined text-[20px]">delete</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            <div class="bg-surface-dark rounded-xl p-6 border border-[#3a2e24]">
                <h3 class="text-white font-bold mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">description</span>
                    Description
                </h3>
                @if($task->description)
                    <p class="text-text-secondary whitespace-pre-line">{{ $task->description }}</p>
                @else
                    <p class="text-text-secondary italic">Aucune description</p>
                @endif
            </div>

            <!-- Progress Update -->
            <div class="bg-surface-dark rounded-xl p-6 border border-[#3a2e24]">
                <h3 class="text-white font-bold mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">trending_up</span>
                    Progression
                </h3>

                @if($task->estimated_hours)
                    <div class="mb-4">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-text-secondary">{{ $task->actual_hours ?? 0 }}h / {{ $task->estimated_hours }}h</span>
                            <span class="text-white font-medium">{{ $task->progress_percent }}%</span>
                        </div>
                        <div class="w-full bg-background-dark rounded-full h-2">
                            <div class="h-full rounded-full bg-primary transition-all" style="width: {{ $task->progress_percent }}%"></div>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-2">Heures reelles</label>
                        <input type="number" wire:model="actual_hours" min="0"
                            class="w-full px-4 py-2 bg-background-dark border border-[#3a2e24] rounded-xl text-white focus:outline-none focus:border-primary"
                            placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-2">Notes</label>
                        <textarea wire:model="notes" rows="2"
                            class="w-full px-4 py-2 bg-background-dark border border-[#3a2e24] rounded-xl text-white focus:outline-none focus:border-primary resize-none"
                            placeholder="Ajouter des notes..."></textarea>
                    </div>
                </div>

                <button wire:click="updateProgress" class="mt-4 bg-primary/10 hover:bg-primary/20 text-primary font-medium py-2 px-4 rounded-xl transition-colors">
                    Mettre a jour la progression
                </button>
            </div>

            <!-- Change Status -->
            <div class="bg-surface-dark rounded-xl p-6 border border-[#3a2e24]">
                <h3 class="text-white font-bold mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">swap_horiz</span>
                    Changer le statut
                </h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($statuses as $status)
                        <button wire:click="updateStatus({{ $status->id }})"
                            class="px-4 py-2 rounded-xl border transition-colors {{ $task->status_id == $status->id ? $status->color_class . ' border-transparent' : 'border-[#3a2e24] text-text-secondary hover:text-white hover:bg-surface-highlight' }}">
                            {{ $status->name }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Details -->
            <div class="bg-surface-dark rounded-xl p-6 border border-[#3a2e24]">
                <h3 class="text-white font-bold mb-4">Details</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-text-secondary text-xs uppercase font-bold">Proprietaire</dt>
                        <dd class="flex items-center gap-2 mt-1">
                            <div class="size-8 rounded-full bg-cover bg-center border border-[#3a2e24]"
                                style='background-image: url("https://ui-avatars.com/api/?name={{ urlencode($task->employee->full_name) }}&background=random&color=fff&size=64");'>
                            </div>
                            <div>
                                <p class="text-white text-sm">{{ $task->employee->full_name }}</p>
                                <p class="text-text-secondary text-xs">{{ $task->employee->job_title }}</p>
                            </div>
                        </dd>
                    </div>

                    <!-- Assignes additionnels -->
                    <div>
                        <dt class="text-text-secondary text-xs uppercase font-bold flex items-center justify-between">
                            <span>Participants</span>
                            <button wire:click="openAssigneeModal" class="text-primary hover:text-primary/80 transition-colors">
                                <span class="material-symbols-outlined text-[18px]">person_add</span>
                            </button>
                        </dt>
                        <dd class="mt-2 space-y-2">
                            @forelse($task->assignees as $assignee)
                                <div class="flex items-center justify-between group">
                                    <div class="flex items-center gap-2">
                                        <div class="size-7 rounded-full bg-cover bg-center border border-[#3a2e24]"
                                            style='background-image: url("https://ui-avatars.com/api/?name={{ urlencode($assignee->full_name) }}&background=random&color=fff&size=64");'>
                                        </div>
                                        <div>
                                            <p class="text-white text-sm">{{ $assignee->full_name }}</p>
                                        </div>
                                    </div>
                                    <button wire:click="removeAssignee({{ $assignee->id }})"
                                        wire:confirm="Retirer {{ $assignee->full_name }} de cette tache ?"
                                        class="opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-300 transition-all">
                                        <span class="material-symbols-outlined text-[16px]">close</span>
                                    </button>
                                </div>
                            @empty
                                <p class="text-text-secondary text-xs italic">Aucun participant</p>
                            @endforelse
                        </dd>
                    </div>

                    @if($task->assignedBy)
                        <div>
                            <dt class="text-text-secondary text-xs uppercase font-bold">Cree par</dt>
                            <dd class="text-white text-sm mt-1">{{ $task->assignedBy->name }}</dd>
                        </div>
                    @endif

                    <div>
                        <dt class="text-text-secondary text-xs uppercase font-bold">Date d'echeance</dt>
                        <dd class="text-white text-sm mt-1">
                            @if($task->due_date)
                                {{ $task->due_date->format('d/m/Y') }}
                                <span class="text-text-secondary">({{ $task->due_date->diffForHumans() }})</span>
                            @else
                                <span class="text-text-secondary">Non definie</span>
                            @endif
                        </dd>
                    </div>

                    @if($task->estimated_hours)
                        <div>
                            <dt class="text-text-secondary text-xs uppercase font-bold">Heures estimees</dt>
                            <dd class="text-white text-sm mt-1">{{ $task->estimated_hours }}h</dd>
                        </div>
                    @endif

                    @if($task->started_at)
                        <div>
                            <dt class="text-text-secondary text-xs uppercase font-bold">Demarree le</dt>
                            <dd class="text-white text-sm mt-1">{{ $task->started_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif

                    @if($task->completed_at)
                        <div>
                            <dt class="text-text-secondary text-xs uppercase font-bold">Terminee le</dt>
                            <dd class="text-green-400 text-sm mt-1">{{ $task->completed_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif

                    <div>
                        <dt class="text-text-secondary text-xs uppercase font-bold">Creee le</dt>
                        <dd class="text-white text-sm mt-1">{{ $task->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    @if($showDeleteModal)
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
                    Etes-vous sur de vouloir supprimer cette tache ?
                </p>
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showDeleteModal', false)" class="px-4 py-2 rounded-xl border border-[#3a2e24] text-text-secondary hover:text-white hover:bg-surface-highlight font-medium transition-colors">
                        Annuler
                    </button>
                    <button wire:click="deleteTask" class="px-4 py-2 rounded-xl bg-red-500 text-white hover:bg-red-600 font-medium transition-colors">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Add Assignee Modal -->
    @if($showAssigneeModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6 max-w-md w-full shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">person_add</span>
                        Ajouter un participant
                    </h3>
                    <button wire:click="closeAssigneeModal" class="text-text-secondary hover:text-white transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-text-secondary mb-2">Selectionner un employe</label>
                    <select wire:model="selectedEmployeeId"
                        class="w-full px-4 py-3 bg-background-dark border border-[#3a2e24] rounded-xl text-white focus:outline-none focus:border-primary">
                        <option value="">-- Choisir un employe --</option>
                        @foreach($availableEmployees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} - {{ $emp->job_title }}</option>
                        @endforeach
                    </select>
                    @error('selectedEmployeeId')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                @if($availableEmployees->isEmpty())
                    <p class="text-text-secondary text-sm mb-4 italic">Aucun employe disponible a ajouter.</p>
                @endif

                <div class="flex justify-end gap-3">
                    <button wire:click="closeAssigneeModal" class="px-4 py-2 rounded-xl border border-[#3a2e24] text-text-secondary hover:text-white hover:bg-surface-highlight font-medium transition-colors">
                        Annuler
                    </button>
                    <button wire:click="addAssignee"
                        @if($availableEmployees->isEmpty()) disabled @endif
                        class="px-4 py-2 rounded-xl bg-primary text-white hover:bg-primary/90 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Ajouter
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
