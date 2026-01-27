<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestion des Taches</h1>
            <p class="text-text-secondary text-sm">Gerez toutes les taches des employes</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('hr.tasks.board') }}" class="bg-surface-dark hover:bg-surface-highlight text-text-secondary hover:text-white font-medium py-2 px-4 rounded-xl flex items-center gap-2 transition-colors border border-[#3a2e24]">
                <span class="material-symbols-outlined text-[20px]">view_kanban</span>
                Vue Kanban
            </a>
            <a href="{{ route('hr.tasks.create') }}" class="bg-primary hover:bg-primary/90 text-white font-bold py-2 px-4 rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Nouvelle Tache
            </a>
        </div>
    </div>

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
            <p class="text-text-secondary text-xs uppercase font-bold">Terminees</p>
            <p class="text-2xl font-bold text-green-400">{{ $stats['completed'] }}</p>
        </div>
        <div class="bg-surface-dark rounded-xl p-4 border border-[#3a2e24]">
            <p class="text-text-secondary text-xs uppercase font-bold">En retard</p>
            <p class="text-2xl font-bold text-red-400">{{ $stats['overdue'] }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-surface-dark rounded-xl p-4 mb-6 border border-[#3a2e24]">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..."
                    class="w-full px-4 py-2 bg-background-dark border border-[#3a2e24] rounded-xl text-white placeholder-text-secondary focus:outline-none focus:border-primary">
            </div>
            <div>
                <select wire:model.live="statusId" class="w-full px-4 py-2 bg-background-dark border border-[#3a2e24] rounded-xl text-white focus:outline-none focus:border-primary">
                    <option value="">Tous les statuts</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="priority" class="w-full px-4 py-2 bg-background-dark border border-[#3a2e24] rounded-xl text-white focus:outline-none focus:border-primary">
                    <option value="">Toutes priorites</option>
                    @foreach(\App\Modules\HR\Models\Task::PRIORITIES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="employeeId" class="w-full px-4 py-2 bg-background-dark border border-[#3a2e24] rounded-xl text-white focus:outline-none focus:border-primary">
                    <option value="">Tous les employes</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="dateFilter" class="w-full px-4 py-2 bg-background-dark border border-[#3a2e24] rounded-xl text-white focus:outline-none focus:border-primary">
                    <option value="">Toutes les dates</option>
                    <option value="today">Aujourd'hui</option>
                    <option value="week">Cette semaine</option>
                    <option value="overdue">En retard</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Tasks Table -->
    <div class="bg-surface-dark rounded-xl border border-[#3a2e24] overflow-hidden">
        <table class="w-full">
            <thead class="bg-background-dark">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-text-secondary uppercase">Tache</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-text-secondary uppercase">Employes</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-text-secondary uppercase">Statut</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-text-secondary uppercase">Priorite</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-text-secondary uppercase">Echeance</th>
                    <th class="px-4 py-3 text-right text-xs font-bold text-text-secondary uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#3a2e24]">
                @forelse($tasks as $task)
                    <tr class="hover:bg-surface-highlight transition-colors {{ $task->is_overdue ? 'bg-red-500/5' : '' }}">
                        <td class="px-4 py-3">
                            <a href="{{ route('hr.tasks.show', $task) }}" class="text-white font-medium hover:text-primary transition-colors">
                                {{ $task->title }}
                            </a>
                            @if($task->description)
                                <p class="text-text-secondary text-xs truncate max-w-xs">{{ Str::limit($task->description, 50) }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                <!-- Avatars en stack -->
                                <div class="flex -space-x-2">
                                    <!-- Proprietaire -->
                                    <div class="size-8 rounded-full bg-cover bg-center border-2 border-surface-dark ring-2 ring-primary/30 z-10"
                                        style='background-image: url("https://ui-avatars.com/api/?name={{ urlencode($task->employee->full_name) }}&background=random&color=fff&size=64");'
                                        title="{{ $task->employee->full_name }} (Proprietaire)">
                                    </div>
                                    <!-- Participants -->
                                    @foreach($task->assignees->take(3) as $assignee)
                                        <div class="size-8 rounded-full bg-cover bg-center border-2 border-surface-dark"
                                            style='background-image: url("https://ui-avatars.com/api/?name={{ urlencode($assignee->full_name) }}&background=random&color=fff&size=64");'
                                            title="{{ $assignee->full_name }}">
                                        </div>
                                    @endforeach
                                    @if($task->assignees->count() > 3)
                                        <div class="size-8 rounded-full bg-surface-highlight border-2 border-surface-dark flex items-center justify-center text-xs text-white font-medium"
                                            title="{{ $task->assignees->count() - 3 }} autres participants">
                                            +{{ $task->assignees->count() - 3 }}
                                        </div>
                                    @endif
                                </div>
                                <!-- Nom du proprietaire -->
                                <div class="ml-3">
                                    <span class="text-white text-sm">{{ $task->employee->full_name }}</span>
                                    @if($task->assignees->count() > 0)
                                        <span class="text-text-secondary text-xs block">+{{ $task->assignees->count() }} participant{{ $task->assignees->count() > 1 ? 's' : '' }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <select wire:change="quickStatusUpdate({{ $task->id }}, $event.target.value)"
                                class="text-xs pl-3 pr-8 py-1.5 rounded-lg border border-[#3a2e24] cursor-pointer appearance-none bg-no-repeat"
                                style="background-color: {{ $task->status->bg_hex }}; color: {{ $task->status->text_hex }}; background-image: url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 fill=%27none%27 viewBox=%270 0 24 24%27 stroke=%27%23888%27%3E%3Cpath stroke-linecap=%27round%27 stroke-linejoin=%27round%27 stroke-width=%272%27 d=%27M19 9l-7 7-7-7%27/%3E%3C/svg%3E'); background-size: 16px; background-position: right 6px center;">
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}" {{ $task->status_id == $status->id ? 'selected' : '' }} style="background-color: #1a1410; color: #fff;">
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-1 rounded-lg {{ $task->priority_class }}">
                                {{ $task->priority_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($task->due_date)
                                <span class="{{ $task->is_overdue ? 'text-red-400' : ($task->is_due_soon ? 'text-amber-400' : 'text-text-secondary') }} text-sm">
                                    {{ $task->due_date->format('d/m/Y') }}
                                </span>
                                @if($task->is_overdue)
                                    <span class="text-red-400 text-xs block">En retard</span>
                                @endif
                            @else
                                <span class="text-text-secondary text-sm">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('hr.tasks.show', $task) }}" class="p-2 rounded-lg hover:bg-background-dark text-text-secondary hover:text-white transition-colors" title="Voir">
                                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                                </a>
                                <a href="{{ route('hr.tasks.edit', $task) }}" class="p-2 rounded-lg hover:bg-background-dark text-text-secondary hover:text-primary transition-colors" title="Modifier">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </a>
                                <button wire:click="confirmDelete({{ $task->id }})" class="p-2 rounded-lg hover:bg-red-500/10 text-text-secondary hover:text-red-400 transition-colors" title="Supprimer">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-text-secondary">
                            Aucune tache trouvee.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $tasks->links() }}
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
