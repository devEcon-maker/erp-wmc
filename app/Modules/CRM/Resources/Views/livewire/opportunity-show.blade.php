<div class="flex flex-col gap-8">
    <!-- Header -->
    <div
        class="flex flex-col md:flex-row justify-between items-start md:items-center p-6 bg-surface-dark border border-[#3a2e24] rounded-2xl gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <span
                    class="px-2.5 py-1 rounded-lg text-xs font-bold text-white bg-{{ $opportunity->stage->color }}-500/20 border border-{{ $opportunity->stage->color }}-500/50">
                    {{ $opportunity->stage->name }}
                </span>
                @if($opportunity->won_at)
                    <span
                        class="px-2.5 py-1 rounded-lg text-xs font-bold text-green-400 bg-green-500/10 border border-green-500/20">GAGNÉE</span>
                @endif
                @if($opportunity->lost_at)
                    <span
                        class="px-2.5 py-1 rounded-lg text-xs font-bold text-red-400 bg-red-500/10 border border-red-500/20">PERDUE</span>
                @endif
            </div>
            <h1 class="text-3xl font-bold text-white">{{ $opportunity->title }}</h1>
            <a href="{{ route('crm.contacts.show', $opportunity->contact) }}"
                class="text-text-secondary hover:text-primary transition-colors flex items-center gap-1 mt-1">
                <span class="material-symbols-outlined text-[18px]">business</span>
                {{ $opportunity->contact->display_name }}
            </a>
        </div>
        <div class="flex flex-wrap gap-3">
            @if(!$opportunity->won_at && !$opportunity->lost_at)
                <button wire:click="markAsLost"
                    class="px-4 py-2 rounded-xl text-red-400 hover:text-white hover:bg-red-500/20 border border-red-500/20 font-bold transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                    Marquer Perdue
                </button>
                <button wire:click="markAsWon"
                    class="px-4 py-2 rounded-xl text-green-400 hover:text-white hover:bg-green-500/20 border border-green-500/20 font-bold transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">check</span>
                    Marquer Gagnée
                </button>
            @endif
            <a href="{{ route('crm.opportunities.edit', $opportunity) }}"
                class="px-4 py-2 rounded-xl bg-surface-highlight text-white hover:bg-[#3a2e24] font-bold transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">edit</span>
                Modifier
            </a>
            <button wire:click="confirmDelete"
                class="px-4 py-2 rounded-xl text-red-400 hover:text-white hover:bg-red-500/20 border border-red-500/20 font-bold transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">delete</span>
                Supprimer
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Details Card -->
            <div class="p-6 bg-surface-dark border border-[#3a2e24] rounded-2xl">
                <h3 class="text-lg font-bold text-white mb-4">Détails de l'opportunité</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-xs text-text-secondary uppercase font-bold mb-1">Montant Estimé</p>
                        <p class="text-xl font-bold text-white">{{ number_format($opportunity->amount, 0, ',', ' ') }}
                            FCFA</p>
                    </div>
                    <div>
                        <p class="text-xs text-text-secondary uppercase font-bold mb-1">Probabilité</p>
                        <p
                            class="text-xl font-bold text-{{ $opportunity->probability >= 50 ? 'green' : 'orange' }}-400">
                            {{ $opportunity->probability }}%</p>
                    </div>
                    <div>
                        <p class="text-xs text-text-secondary uppercase font-bold mb-1">Montant Pondéré</p>
                        <p class="text-xl font-bold text-text-secondary">
                            {{ number_format($opportunity->weighted_amount, 0, ',', ' ') }} FCFA</p>
                    </div>
                    <div>
                        <p class="text-xs text-text-secondary uppercase font-bold mb-1">Date Clôture</p>
                        <p class="text-white font-medium">
                            {{ $opportunity->expected_close_date ? $opportunity->expected_close_date->format('d/m/Y') : 'Non définie' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-text-secondary uppercase font-bold mb-1">Responsable</p>
                        <div class="flex items-center gap-2">
                            @if($opportunity->assignee)
                                <div class="size-6 rounded-full bg-cover bg-center"
                                    style='background-image: url("https://ui-avatars.com/api/?name={{ urlencode($opportunity->assignee->name) }}");'>
                                </div>
                                <span class="text-white font-medium">{{ $opportunity->assignee->name }}</span>
                            @else
                                <span class="text-text-secondary">Non assigné</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mt-6 pt-6 border-t border-[#3a2e24]">
                    <p class="text-xs text-text-secondary uppercase font-bold mb-2">Description</p>
                    <p class="text-text-secondary leading-relaxed">
                        {{ $opportunity->description ?: 'Aucune description disponible.' }}</p>
                </div>
            </div>

            <!-- Pipeline Visualisation -->
            <div class="p-6 bg-surface-dark border border-[#3a2e24] rounded-2xl overflow-x-auto">
                <h3 class="text-lg font-bold text-white mb-6">Pipeline</h3>
                <div class="flex items-center justify-between min-w-[600px] relative">
                    <!-- Progress Line background -->
                    <div class="absolute top-1/2 left-0 w-full h-1 bg-[#251d16] -z-0"></div>

                    @foreach($stages as $stage)
                        @php
                            $isCompleted = $stage->order <= $opportunity->stage->order;
                            $isCurrent = $stage->id === $opportunity->stage_id;
                        @endphp
                        <div class="relative z-10 flex flex-col items-center gap-2 group cursor-default">
                            <div class="size-8 rounded-full flex items-center justify-center border-2 transition-all 
                                    {{ $isCompleted ? 'bg-primary border-primary text-white' : 'bg-background-dark border-[#3a2e24] text-text-secondary' }}
                                    {{ $isCurrent ? 'ring-4 ring-primary/20 scale-110' : '' }}
                                 ">
                                @if($isCompleted && !$isCurrent)
                                    <span class="material-symbols-outlined text-sm">check</span>
                                @elseif($isCurrent)
                                    <div class="size-2.5 rounded-full bg-white animate-pulse"></div>
                                @else
                                    <span class="text-xs font-bold">{{ $stage->order }}</span>
                                @endif
                            </div>
                            <p class="text-xs font-bold {{ $isCompleted ? 'text-white' : 'text-text-secondary' }}">
                                {{ $stage->name }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar Info (History, Proposals, etc can go here) -->
        <div class="space-y-6">
            <div class="p-6 bg-surface-dark border border-[#3a2e24] rounded-2xl">
                <h3 class="text-lg font-bold text-white mb-4">Activité</h3>
                <!-- Placeholder activity timeline -->
                <div class="relative pl-6 border-l border-[#3a2e24] space-y-6">
                    <div class="relative">
                        <div
                            class="absolute -left-[29px] top-1 size-3 rounded-full bg-primary border-4 border-surface-dark">
                        </div>
                        <p class="text-sm text-white">Création de l'opportunité</p>
                        <p class="text-xs text-text-secondary">{{ $opportunity->created_at->diffForHumans() }}</p>
                    </div>
                    @if($opportunity->updated_at != $opportunity->created_at)
                        <div class="relative">
                            <div
                                class="absolute -left-[29px] top-1 size-3 rounded-full bg-[#3a2e24] border-4 border-surface-dark">
                            </div>
                            <p class="text-sm text-white">Dernière modification</p>
                            <p class="text-xs text-text-secondary">{{ $opportunity->updated_at->diffForHumans() }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6 max-w-md w-full shadow-xl">
                <div class="flex items-center gap-3 mb-4">
                    <div class="size-12 rounded-full bg-red-500/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-red-400 text-2xl">warning</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Confirmer la suppression</h3>
                        <p class="text-text-secondary text-sm">Cette action est irréversible</p>
                    </div>
                </div>
                <p class="text-text-secondary mb-6">
                    Êtes-vous sûr de vouloir supprimer l'opportunité <strong class="text-white">"{{ $opportunity->title }}"</strong> ?
                    <br><br>
                    <span class="text-xs">Montant: {{ number_format($opportunity->amount, 0, ',', ' ') }} FCFA</span>
                </p>
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete"
                        class="px-4 py-2 rounded-xl border border-[#3a2e24] text-text-secondary hover:text-white hover:bg-surface-highlight font-medium transition-colors">
                        Annuler
                    </button>
                    <button wire:click="deleteOpportunity"
                        class="px-4 py-2 rounded-xl bg-red-500 text-white hover:bg-red-600 font-medium transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>