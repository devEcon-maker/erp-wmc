<div class="space-y-6">
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">
                Devis et Proforma
            </h2>
            <p class="text-text-secondary text-sm">Gérez vos devis, proforma et convertissez-les en factures.</p>
        </div>
        <div class="flex space-x-3">
            <x-ui.button href="{{ route('crm.proposals.create') }}" type="primary" icon="add">
                Nouveau Devis
            </x-ui.button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Total</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-text-secondary">description</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Brouillons</p>
                    <p class="text-2xl font-bold text-gray-400">{{ $stats['draft'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-gray-500">edit_note</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Envoyés</p>
                    <p class="text-2xl font-bold text-blue-400">{{ $stats['sent'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-blue-500">send</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Acceptés</p>
                    <p class="text-2xl font-bold text-green-400">{{ $stats['accepted'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-green-500">check_circle</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Refusés</p>
                    <p class="text-2xl font-bold text-red-400">{{ $stats['refused'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-red-500">cancel</span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <x-ui.card>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-ui.input wire:model.live.debounce.300ms="search" label="Recherche" placeholder="Référence, Client..." />

            <x-ui.select wire:model.live="status" label="Statut">
                <option value="">Tous les statuts</option>
                <option value="draft">Brouillon</option>
                <option value="sent">Envoyé</option>
                <option value="accepted">Accepté</option>
                <option value="refused">Refusé</option>
            </x-ui.select>
        </div>
    </x-ui.card>

    <!-- Table -->
    <x-ui.card class="overflow-hidden">
        <x-ui.table :headers="['Référence', 'Client', 'Date', 'Montant TTC', 'Statut', 'Documents', 'Actions']">
            @forelse($proposals as $proposal)
                <tr class="hover:bg-surface-highlight/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-white">{{ $proposal->reference }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-text-secondary">{{ $proposal->contact->display_name }}</div>
                        @if($proposal->contact->company_name)
                            <div class="text-xs text-text-secondary opacity-70">{{ $proposal->contact->company_name }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-text-secondary">
                        {{ $proposal->created_at->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-white">
                            {{ number_format($proposal->total_amount_ttc, 0, ',', ' ') }} FCFA</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $statusColors = [
                                'draft' => 'gray',
                                'sent' => 'blue',
                                'accepted' => 'green',
                                'refused' => 'red',
                            ];
                            $statusLabels = [
                                'draft' => 'Brouillon',
                                'sent' => 'Envoyé',
                                'accepted' => 'Accepté',
                                'refused' => 'Refusé',
                            ];
                        @endphp
                        <x-ui.badge :color="$statusColors[$proposal->status] ?? 'gray'">
                            {{ $statusLabels[$proposal->status] ?? ucfirst($proposal->status) }}
                        </x-ui.badge>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex gap-2">
                            @if($proposal->order)
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-purple-500/20 text-purple-400" title="Bon de commande: {{ $proposal->order->reference }}">
                                    <span class="material-symbols-outlined text-[14px] mr-1">shopping_cart</span>
                                    {{ $proposal->order->reference }}
                                </span>
                            @endif
                            @if($proposal->invoice)
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-500/20 text-green-400" title="Facture: {{ $proposal->invoice->reference }}">
                                    <span class="material-symbols-outlined text-[14px] mr-1">receipt</span>
                                    {{ $proposal->invoice->reference }}
                                </span>
                            @endif
                            @if(!$proposal->order && !$proposal->invoice)
                                <span class="text-xs text-text-secondary">-</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-1" x-data="{ showActions: false }">
                            <a href="{{ route('crm.proposals.show', $proposal) }}"
                                class="p-2 rounded-lg hover:bg-surface-highlight text-text-secondary hover:text-primary transition-colors"
                                title="Voir">
                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                            </a>
                            <a href="{{ route('crm.proposals.edit', $proposal) }}"
                                class="p-2 rounded-lg hover:bg-surface-highlight text-text-secondary hover:text-orange-400 transition-colors"
                                title="Modifier">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </a>

                            <!-- Menu déroulant Actions -->
                            <div class="relative">
                                <button @click="showActions = !showActions"
                                    class="p-2 rounded-lg hover:bg-surface-highlight text-text-secondary hover:text-white transition-colors"
                                    title="Plus d'actions">
                                    <span class="material-symbols-outlined text-[20px]">more_vert</span>
                                </button>
                                <div x-show="showActions"
                                    @click.away="showActions = false"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    class="absolute right-0 mt-2 w-56 rounded-xl bg-surface-dark border border-[#3a2e24] shadow-lg z-50">
                                    <div class="py-1">
                                        @if($proposal->status === 'draft')
                                            <button wire:click="markAsSent({{ $proposal->id }})" @click="showActions = false"
                                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-text-secondary hover:bg-surface-highlight hover:text-blue-400 transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">send</span>
                                                Marquer comme envoyé
                                            </button>
                                        @endif

                                        @if(in_array($proposal->status, ['draft', 'sent']))
                                            <button wire:click="markAsAccepted({{ $proposal->id }})" @click="showActions = false"
                                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-text-secondary hover:bg-surface-highlight hover:text-green-400 transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                                Accepter
                                            </button>
                                            <button wire:click="markAsRefused({{ $proposal->id }})" @click="showActions = false"
                                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-text-secondary hover:bg-surface-highlight hover:text-red-400 transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">cancel</span>
                                                Refuser
                                            </button>
                                        @endif

                                        @if($proposal->status === 'accepted')
                                            @if(!$proposal->order)
                                                <button wire:click="convertToOrder({{ $proposal->id }})" @click="showActions = false"
                                                    class="w-full flex items-center gap-2 px-4 py-2 text-sm text-text-secondary hover:bg-surface-highlight hover:text-purple-400 transition-colors">
                                                    <span class="material-symbols-outlined text-[18px]">shopping_cart</span>
                                                    Créer Bon de Commande
                                                </button>
                                            @endif

                                            @if(!$proposal->invoice)
                                                <button wire:click="convertToInvoice({{ $proposal->id }})" @click="showActions = false"
                                                    class="w-full flex items-center gap-2 px-4 py-2 text-sm text-text-secondary hover:bg-surface-highlight hover:text-green-400 transition-colors">
                                                    <span class="material-symbols-outlined text-[18px]">receipt</span>
                                                    Convertir en Facture
                                                </button>
                                            @else
                                                <span class="w-full flex items-center gap-2 px-4 py-2 text-sm text-green-500/70">
                                                    <span class="material-symbols-outlined text-[18px]">check</span>
                                                    Facture créée
                                                </span>
                                            @endif
                                        @endif

                                        @if($proposal->status === 'refused')
                                            <span class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-400/70">
                                                <span class="material-symbols-outlined text-[18px]">block</span>
                                                Devis refusé
                                            </span>
                                        @endif

                                        <div class="border-t border-[#3a2e24] my-1"></div>

                                        <!-- Supprimer -->
                                        <button wire:click="confirmDelete({{ $proposal->id }})" @click="showActions = false"
                                            class="w-full flex items-center gap-2 px-4 py-2 text-sm text-text-secondary hover:bg-red-500/10 hover:text-red-400 transition-colors">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                            Supprimer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-text-secondary">
                        <div class="flex flex-col items-center justify-center">
                            <span class="material-symbols-outlined text-4xl mb-2 opacity-50">description</span>
                            <p>Aucun devis trouvé.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-ui.table>
        <div class="border-t border-[#3a2e24] p-4">
            {{ $proposals->links() }}
        </div>
    </x-ui.card>

    <!-- Modal de confirmation de suppression -->
    @if($showDeleteModal && $proposalToDelete)
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
                    Êtes-vous sûr de vouloir supprimer le devis <strong class="text-white">{{ $proposalToDelete->reference }}</strong> ?
                    <br><br>
                    <span class="text-xs">Montant: {{ number_format($proposalToDelete->total_amount_ttc, 0, ',', ' ') }} FCFA</span>
                </p>
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete"
                        class="px-4 py-2 rounded-xl border border-[#3a2e24] text-text-secondary hover:text-white hover:bg-surface-highlight font-medium transition-colors">
                        Annuler
                    </button>
                    <button wire:click="deleteProposal"
                        class="px-4 py-2 rounded-xl bg-red-500 text-white hover:bg-red-600 font-medium transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
