<div class="flex flex-col h-full overflow-hidden">
    <!-- Header / Stats -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-6 border-b border-[#3a2e24] bg-background-dark gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Pipeline Opportunités</h1>
            <p class="text-text-secondary text-sm">Gérez votre cycle de vente par glisser-déposer.</p>
        </div>
        <div class="flex gap-4">
            <div class="px-4 py-2 bg-surface-dark rounded-xl border border-[#3a2e24]">
                <p class="text-xs text-text-secondary uppercase font-bold">Total Pipeline</p>
                <p class="text-lg font-bold text-white">{{ number_format($stats['total_amount'], 0, ',', ' ') }} FCFA</p>
            </div>
            <div class="px-4 py-2 bg-surface-dark rounded-xl border border-[#3a2e24]">
                <p class="text-xs text-text-secondary uppercase font-bold">Pondéré</p>
                <p class="text-lg font-bold text-primary">{{ number_format($stats['weighted_amount'], 0, ',', ' ') }} FCFA</p>
            </div>
            <a href="{{ route('crm.opportunities.create') }}" class="bg-primary hover:bg-primary/90 text-white font-bold py-2 px-4 rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-[20px]">add</span>
                <span class="hidden sm:inline">Nouvelle</span>
            </a>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="flex-1 overflow-x-auto overflow-y-hidden p-6">
        <div class="flex h-full gap-6 min-w-[max-content]">
            @foreach ($stages_list as $stage)
                <div class="w-80 flex flex-col h-full bg-surface-dark rounded-2xl border border-[#3a2e24] flex-shrink-0">
                    <!-- Column Header -->
                    <div class="p-4 border-b border-[#3a2e24] flex justify-between items-center sticky top-0 bg-surface-dark rounded-t-2xl z-10">
                        <div class="flex items-center gap-2">
                            <div class="size-3 rounded-full bg-{{ $stage->color }}-500"></div>
                            <h3 class="font-bold text-white text-sm">{{ $stage->name }}</h3>
                        </div>
                        <span class="text-xs font-bold text-text-secondary bg-background-dark px-2 py-1 rounded-lg">
                            {{ $this->getOpportunitiesForStage($stage->id)->count() }}
                        </span>
                    </div>

                    <!-- Draggable Area -->
                    <div
                        class="flex-1 overflow-y-auto p-3 space-y-3 custom-scrollbar kanban-column"
                        data-stage-id="{{ $stage->id }}"
                    >
                        @foreach ($this->getOpportunitiesForStage($stage->id) as $opportunity)
                            <div
                                data-id="{{ $opportunity->id }}"
                                class="bg-background-dark p-4 rounded-xl border border-[#3a2e24] hover:border-primary/50 cursor-grab active:cursor-grabbing shadow-sm group transition-all opportunity-card relative"
                            >
                                <div class="flex justify-between items-start mb-2">
                                    <a href="{{ route('crm.opportunities.show', $opportunity) }}" class="text-white font-bold text-sm leading-tight group-hover:text-primary transition-colors line-clamp-2 flex-1 pr-2">
                                        {{ $opportunity->title }}
                                    </a>
                                    <!-- Action buttons -->
                                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('crm.opportunities.edit', $opportunity) }}"
                                            class="size-6 rounded-lg bg-surface-highlight hover:bg-primary/20 flex items-center justify-center text-text-secondary hover:text-primary transition-colors"
                                            title="Modifier">
                                            <span class="material-symbols-outlined text-[14px]">edit</span>
                                        </a>
                                        <button wire:click="confirmDelete({{ $opportunity->id }})"
                                            class="size-6 rounded-lg bg-surface-highlight hover:bg-red-500/20 flex items-center justify-center text-text-secondary hover:text-red-400 transition-colors"
                                            title="Supprimer">
                                            <span class="material-symbols-outlined text-[14px]">delete</span>
                                        </button>
                                    </div>
                                </div>

                                <p class="text-text-secondary text-xs mb-3 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">business</span>
                                    {{ $opportunity->contact?->display_name ?? 'Sans contact' }}
                                </p>

                                <div class="flex justify-between items-end mt-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs text-text-secondary">Montant</span>
                                        <span class="text-white font-bold text-sm">{{ number_format($opportunity->amount, 0, ',', ' ') }} FCFA</span>
                                    </div>
                                    @if($opportunity->assignee)
                                        <abbr title="{{ $opportunity->assignee->name }}">
                                            <div class="size-6 rounded-full bg-cover bg-center border border-[#3a2e24]"
                                                style='background-image: url("https://ui-avatars.com/api/?name={{ urlencode($opportunity->assignee->name) }}&background=random&color=fff&size=64");'>
                                            </div>
                                        </abbr>
                                    @endif
                                </div>

                                <!-- Probability Bar -->
                                <div class="mt-3 w-full bg-[#3a2e24] rounded-full h-1.5 overflow-hidden">
                                    <div
                                        class="h-full rounded-full transition-all duration-500 {{ $opportunity->probability >= 80 ? 'bg-green-500' : ($opportunity->probability >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                        style="width: {{ $opportunity->probability }}%"
                                    ></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Column Footer (Total) -->
                    <div class="p-3 border-t border-[#3a2e24] bg-surface-dark/50 text-center">
                        <p class="text-xs text-text-secondary font-medium">
                            Total: {{ number_format($this->getOpportunitiesForStage($stage->id)->sum('amount'), 0, ',', ' ') }} FCFA
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

    <!-- Modal de confirmation de suppression -->
    @if($showDeleteModal && $opportunityToDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" wire:ignore.self>
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
                    Êtes-vous sûr de vouloir supprimer l'opportunité <strong class="text-white">"{{ $opportunityToDelete->title }}"</strong> ?
                </p>
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete"
                        class="px-4 py-2 rounded-xl border border-[#3a2e24] text-text-secondary hover:text-white hover:bg-surface-highlight font-medium transition-colors">
                        Annuler
                    </button>
                    <button wire:click="deleteOpportunity"
                        class="px-4 py-2 rounded-xl bg-red-500 text-white hover:bg-red-600 font-medium transition-colors">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    @endif

@script
<script>
    document.addEventListener('livewire:navigated', initSortable);
    document.addEventListener('DOMContentLoaded', initSortable);

    function initSortable() {
        document.querySelectorAll('.kanban-column').forEach(column => {
            // Éviter la double initialisation
            if (column.sortableInstance) return;

            column.sortableInstance = Sortable.create(column, {
                group: 'opportunities',
                animation: 150,
                ghostClass: 'opacity-50',
                dragClass: 'dragging',
                chosenClass: 'chosen',
                onEnd: function(evt) {
                    const opportunityId = evt.item.getAttribute('data-id');
                    const newStageId = evt.to.getAttribute('data-stage-id');

                    if (opportunityId && newStageId) {
                        // Appeler directement la méthode Livewire
                        @this.call('updateStage', opportunityId, newStageId);
                    }
                }
            });
        });
    }

    // Initialiser au chargement
    initSortable();
</script>
@endscript
