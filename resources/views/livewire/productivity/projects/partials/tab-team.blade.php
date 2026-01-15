<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold text-white">Équipe du projet</h3>
            <p class="text-sm text-text-secondary">{{ $project->members->count() }} membres</p>
        </div>
        <button wire:click="openMemberModal" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/80 flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">person_add</span>
            Ajouter un membre
        </button>
    </div>

    <!-- Manager -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
        <h4 class="text-sm font-medium text-text-secondary uppercase tracking-wide mb-4">Manager du projet</h4>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-primary/20 flex items-center justify-center text-lg font-medium text-primary">
                    {{ substr($project->manager->first_name ?? '', 0, 1) }}{{ substr($project->manager->last_name ?? '', 0, 1) }}
                </div>
                <div>
                    <p class="text-lg font-medium text-white">{{ $project->manager?->full_name ?? '-' }}</p>
                    <p class="text-sm text-text-secondary">{{ $project->manager?->position ?? 'Manager' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($project->manager?->email)
                    <a href="mailto:{{ $project->manager->email }}"
                        class="p-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg transition-colors">
                        <span class="material-symbols-outlined">mail</span>
                    </a>
                @endif
                @if($project->manager?->phone)
                    <a href="tel:{{ $project->manager->phone }}"
                        class="p-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg transition-colors">
                        <span class="material-symbols-outlined">call</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Members -->
    @if($project->members->count() > 0)
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-[#3a2e24]">
                <h4 class="text-sm font-medium text-text-secondary uppercase tracking-wide">Membres de l'équipe</h4>
            </div>

            <div class="divide-y divide-[#3a2e24]">
                @foreach($project->members as $member)
                    <div class="p-4 hover:bg-surface-highlight transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-primary/20 flex items-center justify-center text-sm font-medium text-primary">
                                    {{ substr($member->first_name, 0, 1) }}{{ substr($member->last_name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-medium text-white">{{ $member->full_name }}</p>
                                    <div class="flex items-center gap-3 text-sm text-text-secondary">
                                        @if($member->pivot->role)
                                            <span class="flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[14px]">badge</span>
                                                {{ $member->pivot->role }}
                                            </span>
                                        @endif
                                        @if($member->pivot->hourly_rate)
                                            <span class="flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[14px]">payments</span>
                                                {{ number_format($member->pivot->hourly_rate, 2) }} FCFA/h
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <!-- Stats -->
                                @php
                                    $memberTasks = $project->tasks->where('assigned_to', $member->id);
                                    $memberHours = $project->timeEntries->where('employee_id', $member->id)->sum('hours');
                                @endphp
                                <div class="text-right text-sm">
                                    <p class="text-white">{{ $memberTasks->count() }} tâches</p>
                                    <p class="text-text-secondary">{{ number_format($memberHours, 1) }}h</p>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-1">
                                    @if($member->email)
                                        <a href="mailto:{{ $member->email }}"
                                            class="p-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg transition-colors">
                                            <span class="material-symbols-outlined text-[20px]">mail</span>
                                        </a>
                                    @endif
                                    <button wire:click="removeMember({{ $member->id }})"
                                        wire:confirm="Retirer {{ $member->full_name }} du projet ?"
                                        class="p-2 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition-colors">
                                        <span class="material-symbols-outlined text-[20px]">person_remove</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-12 text-center">
            <span class="material-symbols-outlined text-5xl text-text-secondary mb-4">group</span>
            <h3 class="text-lg font-medium text-white mb-2">Aucun membre</h3>
            <p class="text-text-secondary mb-4">Ajoutez des membres à ce projet.</p>
            <button wire:click="openMemberModal" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/80 inline-flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">person_add</span>
                Ajouter un membre
            </button>
        </div>
    @endif
</div>
