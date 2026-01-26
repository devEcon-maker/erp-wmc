<div class="space-y-6">
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">
                Factures
            </h2>
            <p class="text-text-secondary text-sm">Gestion de la facturation et des paiements.</p>
        </div>
        <div class="flex space-x-3">
            <x-ui.button wire:click="processReminders" wire:loading.attr="disabled" type="secondary"
                icon="notifications_active">
                Lancer les relances
            </x-ui.button>
            <x-ui.button href="{{ route('finance.invoices.create') }}" type="primary" icon="add">
                Nouvelle Facture
            </x-ui.button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-surface-dark border border-[#3a2e24] p-4 rounded-xl">
            <div class="text-sm text-text-secondary mb-1">Total Dû (Estimé)</div>
            <div class="text-2xl font-bold text-white">{{ number_format($stats['total_due'], 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] p-4 rounded-xl">
            <div class="text-sm text-text-secondary mb-1">Factures En Retard</div>
            <div class="text-2xl font-bold text-red-500 flex items-center gap-2">
                {{ $stats['overdue_count'] }}
                <span
                    class="text-xs font-normal text-text-secondary bg-surface-highlight px-2 py-0.5 rounded-full">Relancer</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] p-4 rounded-xl">
            <div class="text-sm text-text-secondary mb-1">Encaissé ce mois</div>
            <div class="text-2xl font-bold text-green-500">{{ number_format($stats['paid_month'], 0, ',', ' ') }} FCFA
            </div>
        </div>
        <button wire:click="$toggle('showDeleted')"
            class="bg-surface-dark border border-[#3a2e24] p-4 rounded-xl text-left hover:bg-surface-highlight transition-colors {{ $showDeleted ? 'ring-2 ring-red-500/50' : '' }}">
            <div class="text-sm text-text-secondary mb-1">Factures Supprimées</div>
            <div class="text-2xl font-bold {{ $showDeleted ? 'text-red-400' : 'text-text-secondary' }} flex items-center gap-2">
                {{ $stats['deleted_count'] }}
                @if($showDeleted)
                    <span class="text-xs font-normal text-red-400 bg-red-500/10 px-2 py-0.5 rounded-full">Affiché</span>
                @else
                    <span class="text-xs font-normal text-text-secondary bg-surface-highlight px-2 py-0.5 rounded-full">Voir</span>
                @endif
            </div>
        </button>
    </div>

    <!-- Filters -->
    <x-ui.card>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-ui.input wire:model.live.debounce.300ms="search" label="Recherche" placeholder="N° Facture, Client..." />

            <x-ui.select wire:model.live="status" label="Statut">
                <option value="">Tous les statuts</option>
                <option value="draft">Brouillon</option>
                <option value="sent">Envoyée</option>
                <option value="partial">Partiellement Payée</option>
                <option value="paid">Payée</option>
                <option value="overdue">En Retard</option>
                <option value="cancelled">Annulée</option>
            </x-ui.select>

            <x-ui.select wire:model.live="due_filter" label="Échéance">
                <option value="">Toutes les dates</option>
                <option value="overdue">En retard uniquement</option>
            </x-ui.select>
        </div>
    </x-ui.card>

    <!-- Table -->
    <x-ui.card class="overflow-hidden">
        @if($showDeleted)
            {{-- Tableau des factures supprimées --}}
            <div class="bg-red-500/10 border-b border-red-500/20 p-4">
                <div class="flex items-center gap-2 text-red-400">
                    <span class="material-symbols-outlined">delete</span>
                    <span class="font-medium">Historique des factures supprimées</span>
                </div>
            </div>
            <x-ui.table :headers="['Référence', 'Client', 'Montant TTC', 'Supprimée le', 'Supprimée par', 'Raison']">
                @forelse($invoices as $invoice)
                    <tr class="hover:bg-surface-highlight/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-red-400 line-through">{{ $invoice->reference }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-text-secondary">{{ $invoice->contact->display_name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-text-secondary">
                                {{ number_format($invoice->total_amount_ttc, 0, ',', ' ') }} FCFA
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="text-red-400 font-medium">
                                {{ $invoice->deleted_at->format('d/m/Y') }}
                            </div>
                            <div class="text-xs text-text-secondary">
                                {{ $invoice->deleted_at->format('H:i') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-text-secondary">
                            @if($invoice->deleter)
                                <div class="flex items-center gap-2">
                                    <div class="size-6 rounded-full bg-red-500/20 flex items-center justify-center text-red-400 text-xs font-bold">
                                        {{ substr($invoice->deleter->name, 0, 1) }}
                                    </div>
                                    <span>{{ $invoice->deleter->name }}</span>
                                </div>
                            @else
                                <span class="text-text-secondary italic">Non enregistré</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-text-secondary max-w-xs">
                            @if($invoice->deletion_reason)
                                <div class="truncate" title="{{ $invoice->deletion_reason }}">
                                    {{ $invoice->deletion_reason }}
                                </div>
                            @else
                                <span class="italic">Non spécifiée</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-text-secondary">
                            <div class="flex flex-col items-center justify-center">
                                <span class="material-symbols-outlined text-4xl mb-2 opacity-50">delete_forever</span>
                                <p>Aucune facture supprimée.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        @else
            {{-- Tableau normal des factures --}}
            <x-ui.table :headers="['Référence', 'Client', 'Date', 'Échéance', 'Montant TTC', 'Reste Dû', 'Statut', 'Actions']">
                @forelse($invoices as $invoice)
                    <tr class="hover:bg-surface-highlight/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-white">{{ $invoice->reference }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-text-secondary">{{ $invoice->contact->display_name }}</div>
                            @if($invoice->contact->company_name)
                                <div class="text-xs text-text-secondary opacity-70">{{ $invoice->contact->company_name }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-text-secondary">
                            {{ $invoice->order_date->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-text-secondary">
                            <div class="{{ $invoice->status === 'overdue' ? 'text-red-500 font-bold' : '' }}">
                                {{ $invoice->due_date->format('d/m/Y') }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-white">
                                {{ number_format($invoice->total_amount_ttc, 0, ',', ' ') }} FCFA
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($invoice->status === 'paid')
                                <span class="text-green-500 text-xs font-bold">Soldé</span>
                            @else
                                <div class="text-sm font-medium text-orange-400">
                                    {{ number_format($invoice->remaining_balance, 0, ',', ' ') }} FCFA
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'draft' => 'gray',
                                    'sent' => 'blue',
                                    'partial' => 'orange',
                                    'paid' => 'green',
                                    'overdue' => 'red',
                                    'cancelled' => 'black',
                                ];
                                $statusLabels = [
                                    'draft' => 'Brouillon',
                                    'sent' => 'Envoyée',
                                    'partial' => 'Partiel',
                                    'paid' => 'Payée',
                                    'overdue' => 'En Retard',
                                    'cancelled' => 'Annulée',
                                ];
                            @endphp
                            <x-ui.badge :color="$statusColors[$invoice->status] ?? 'gray'">
                                {{ $statusLabels[$invoice->status] ?? ucfirst($invoice->status) }}
                            </x-ui.badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('finance.invoices.show', $invoice) }}"
                                    class="p-2 rounded-lg hover:bg-surface-highlight text-text-secondary hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                </a>
                                @if($invoice->status === 'draft')
                                    <a href="{{ route('finance.invoices.edit', $invoice) }}"
                                        class="p-2 rounded-lg hover:bg-surface-highlight text-text-secondary hover:text-orange-400 transition-colors">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-text-secondary">
                            <div class="flex flex-col items-center justify-center">
                                <span class="material-symbols-outlined text-4xl mb-2 opacity-50">receipt_long</span>
                                <p>Aucune facture trouvée.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        @endif
        <div class="border-t border-[#3a2e24] p-4">
            {{ $invoices->links() }}
        </div>
    </x-ui.card>
</div>