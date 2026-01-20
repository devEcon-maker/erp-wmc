<div class="space-y-6">
    <!-- Header -->
    <div
        class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 rounded-xl bg-primary/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-3xl text-primary">folder</span>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-white">{{ $project->name }}</h2>
                @if($project->contact)
                    <p class="text-text-secondary flex items-center gap-1">
                        <span class="material-symbols-outlined text-[18px]">business</span>
                        {{ $project->contact->company_name }}
                    </p>
                @endif
                <div class="flex items-center gap-2 mt-2">
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
                        $billingLabels = [
                            'fixed' => 'Forfait',
                            'hourly' => 'Horaire',
                            'non_billable' => 'Non facturable',
                        ];
                    @endphp
                    <span class="px-2 py-1 text-xs rounded-full border {{ $statusColors[$project->status] ?? '' }}">
                        {{ $statusLabels[$project->status] ?? $project->status }}
                    </span>
                    <span
                        class="px-2 py-1 text-xs rounded-full bg-surface-highlight text-text-secondary border border-[#3a2e24]">
                        {{ $billingLabels[$project->billing_type] ?? $project->billing_type }}
                    </span>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-ui.button type="secondary" href="{{ route('productivity.projects.index') }}"
                class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                Retour
            </x-ui.button>
            <x-ui.button type="secondary" href="{{ route('productivity.projects.edit', $project) }}"
                class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">edit</span>
                Modifier
            </x-ui.button>
            <x-ui.button href="{{ route('productivity.projects.tasks', $project) }}" class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">view_kanban</span>
                Kanban
            </x-ui.button>

            <!-- Actions Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <x-ui.button type="secondary" @click="open = !open" class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">more_vert</span>
                </x-ui.button>
                <div x-show="open" @click.away="open = false" x-transition
                    class="absolute right-0 mt-2 w-48 bg-surface-dark border border-[#3a2e24] rounded-lg shadow-xl z-50">
                    @if($project->status === 'planning')
                        <button wire:click="activate"
                            class="w-full px-4 py-2 text-left text-sm text-white hover:bg-surface-highlight flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-green-400">play_arrow</span>
                            Activer
                        </button>
                    @endif
                    @if($project->status === 'active')
                        <button wire:click="putOnHold"
                            class="w-full px-4 py-2 text-left text-sm text-white hover:bg-surface-highlight flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-yellow-400">pause</span>
                            Mettre en pause
                        </button>
                        <button wire:click="complete"
                            class="w-full px-4 py-2 text-left text-sm text-white hover:bg-surface-highlight flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-purple-400">check_circle</span>
                            Terminer
                        </button>
                    @endif
                    @if($project->status === 'on_hold')
                        <button wire:click="activate"
                            class="w-full px-4 py-2 text-left text-sm text-white hover:bg-surface-highlight flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-green-400">play_arrow</span>
                            Reprendre
                        </button>
                    @endif
                    @if(!in_array($project->status, ['completed', 'cancelled']))
                        <button wire:click="cancel" wire:confirm="Êtes-vous sûr de vouloir annuler ce projet ?"
                            class="w-full px-4 py-2 text-left text-sm text-red-400 hover:bg-surface-highlight flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">cancel</span>
                            Annuler
                        </button>
                    @endif
                    <div class="border-t border-[#3a2e24]"></div>
                    <button wire:click="delete"
                        wire:confirm="Êtes-vous sûr de vouloir supprimer ce projet ? Cette action est irréversible."
                        class="w-full px-4 py-2 text-left text-sm text-red-400 hover:bg-surface-highlight flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
        <div class="flex justify-between items-center mb-2">
            <span class="text-sm text-text-secondary">Progression globale</span>
            <span class="text-sm font-medium text-white">{{ $tasksStats['progress'] }}%</span>
        </div>
        <div class="w-full h-3 bg-surface-highlight rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-primary to-primary/70 rounded-full transition-all duration-500"
                style="width: {{ $tasksStats['progress'] }}%"></div>
        </div>
        <div class="flex justify-between items-center mt-2 text-xs text-text-secondary">
            <span>{{ $tasksStats['done'] }}/{{ $tasksStats['total'] }} tâches terminées</span>
            @if($project->end_date)
                <span>Échéance: {{ $project->end_date->format('d/m/Y') }}</span>
            @endif
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-[#3a2e24]">
        <nav class="flex gap-1">
            @php
                $tabs = [
                    'overview' => ['label' => 'Vue d\'ensemble', 'icon' => 'dashboard'],
                    'tasks' => ['label' => 'Tâches', 'icon' => 'task'],
                    'time' => ['label' => 'Temps', 'icon' => 'schedule'],
                    'team' => ['label' => 'Équipe', 'icon' => 'group'],
                    'finances' => ['label' => 'Finances', 'icon' => 'payments'],
                ];
            @endphp
            @foreach($tabs as $key => $tab)
                    <button wire:click="setTab('{{ $key }}')" class="px-4 py-3 text-sm font-medium transition-colors flex items-center gap-2
                                {{ $activeTab === $key
                ? 'text-primary border-b-2 border-primary bg-primary/10'
                : 'text-text-secondary hover:text-white hover:bg-surface-highlight' }}">
                        <span class="material-symbols-outlined text-[20px]">{{ $tab['icon'] }}</span>
                        {{ $tab['label'] }}
                    </button>
            @endforeach
        </nav>
    </div>

    <!-- Tab Content -->
    @if($activeTab === 'overview')
        @include('livewire.productivity.projects.partials.tab-overview')
    @elseif($activeTab === 'tasks')
        @include('livewire.productivity.projects.partials.tab-tasks')
    @elseif($activeTab === 'time')
        @include('livewire.productivity.projects.partials.tab-time')
    @elseif($activeTab === 'team')
        @include('livewire.productivity.projects.partials.tab-team')
    @elseif($activeTab === 'finances')
        @include('livewire.productivity.projects.partials.tab-finances')
    @endif

    <!-- Member Modal -->
    @if($showMemberModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="closeMemberModal">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Ajouter un membre</h3>

                <div class="space-y-4">
                    <x-ui.select label="Employé *" wire:model="newMember.employee_id"
                        :error="$errors->first('newMember.employee_id')">
                        <option value="">Sélectionner un employé</option>
                        @foreach($availableEmployees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.input label="Rôle" wire:model="newMember.role" placeholder="Ex: Développeur, Designer..." />

                    <x-ui.input type="number" step="0.01" label="Taux horaire (FCFA)" wire:model="newMember.hourly_rate"
                        placeholder="Laisser vide pour utiliser le taux du projet" />
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <x-ui.button type="secondary" wire:click="closeMemberModal">Annuler</x-ui.button>
                    <x-ui.button wire:click="addMember">Ajouter</x-ui.button>
                </div>
            </div>
        </div>
    @endif

    <!-- Quick Task Modal -->
    @if($showQuickTaskModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="closeQuickTaskModal">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Créer une tâche rapide</h3>

                <div class="space-y-4">
                    <x-ui.input label="Titre *" wire:model="quickTask.title" placeholder="Titre de la tâche"
                        :error="$errors->first('quickTask.title')" />

                    <x-ui.select label="Assigné à" wire:model="quickTask.assigned_to">
                        <option value="">Non assigné</option>
                        @if($project->manager)
                            <option value="{{ $project->manager->id }}">{{ $project->manager->full_name }} (Manager)</option>
                        @endif
                        @foreach($project->members as $member)
                            @if($project->manager_id !== $member->id)
                                <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                            @endif
                        @endforeach
                    </x-ui.select>

                    <x-ui.select label="Priorité" wire:model="quickTask.priority">
                        <option value="low">Basse</option>
                        <option value="medium">Moyenne</option>
                        <option value="high">Haute</option>
                        <option value="urgent">Urgente</option>
                    </x-ui.select>

                    <x-ui.input type="date" label="Date d'échéance" wire:model="quickTask.due_date" />
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <x-ui.button type="secondary" wire:click="closeQuickTaskModal">Annuler</x-ui.button>
                    <x-ui.button wire:click="createQuickTask">Créer</x-ui.button>
                </div>
            </div>
        </div>
    @endif
</div>