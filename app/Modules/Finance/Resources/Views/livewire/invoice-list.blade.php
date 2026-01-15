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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
        <div class="border-t border-[#3a2e24] p-4">
            {{ $invoices->links() }}
        </div>
    </x-ui.card>
</div>