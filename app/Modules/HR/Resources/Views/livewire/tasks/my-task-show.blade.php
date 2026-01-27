<div class="p-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <a href="{{ route('hr.my-tasks') }}" class="text-text-secondary hover:text-white text-sm flex items-center gap-1 mb-2 transition-colors">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Retour a mes taches
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
            <a href="{{ route('hr.my-tasks.edit', $task) }}" class="bg-surface-dark hover:bg-surface-highlight text-white font-medium py-2 px-4 rounded-xl flex items-center gap-2 transition-colors border border-[#3a2e24]">
                <span class="material-symbols-outlined text-[20px]">edit</span>
                Modifier
            </a>
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

                    <!-- Participants -->
                    @if($task->assignees->count() > 0)
                        <div>
                            <dt class="text-text-secondary text-xs uppercase font-bold">Participants</dt>
                            <dd class="mt-2 space-y-2">
                                @foreach($task->assignees as $assignee)
                                    <div class="flex items-center gap-2">
                                        <div class="size-7 rounded-full bg-cover bg-center border border-[#3a2e24]"
                                            style='background-image: url("https://ui-avatars.com/api/?name={{ urlencode($assignee->full_name) }}&background=random&color=fff&size=64");'>
                                        </div>
                                        <div>
                                            <p class="text-white text-sm">{{ $assignee->full_name }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </dd>
                        </div>
                    @endif

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
</div>
