<div class="space-y-6">
    <!-- Header -->
    <div
        class="flex flex-col md:flex-row justify-between items-start md:items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <h2 class="text-2xl font-bold text-white tracking-tight">
                    Devis {{ $proposal->reference }}
                </h2>
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
            </div>
            <p class="text-text-secondary text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-[16px]">business</span>
                {{ $proposal->contact->display_name }}
                <span class="text-[#3a2e24]">|</span>
                <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                {{ $proposal->created_at->format('d/m/Y') }}
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <x-ui.button href="{{ route('crm.proposals.edit', $proposal) }}" type="secondary" icon="edit">
                Modifier
            </x-ui.button>
            <x-ui.button href="{{ route('crm.proposals.pdf', $proposal) }}" type="secondary" icon="print">
                Imprimer PDF
            </x-ui.button>

            @if($proposal->status === 'draft')
                <x-ui.button wire:click="markAsSent" type="primary" icon="send">
                    Marquer Envoyé
                </x-ui.button>
            @elseif($proposal->status === 'sent')
                <x-ui.button wire:click="markAsAccepted" type="success" icon="check_circle">
                    Accepter
                </x-ui.button>
                <x-ui.button wire:click="markAsRefused" type="danger" icon="cancel">
                    Refuser
                </x-ui.button>
            @endif

            <button wire:click="confirmDelete"
                class="px-4 py-2 rounded-xl text-red-400 hover:text-white hover:bg-red-500/20 border border-red-500/20 font-bold transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">delete</span>
                Supprimer
            </button>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Document -->
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card class="overflow-hidden">
                <!-- Lines Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-sm text-text-secondary border-b border-[#3a2e24]">
                                <th class="p-4 font-bold uppercase">Description</th>
                                <th class="p-4 font-bold uppercase text-right">Prix Unitaire</th>
                                <th class="p-4 font-bold uppercase text-center">Qté</th>
                                <th class="p-4 font-bold uppercase text-right">Total HT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#3a2e24]">
                            @foreach($proposal->lines as $line)
                                <tr class="text-white hover:bg-surface-highlight/20">
                                    <td class="p-4">
                                        <div class="font-bold">{{ $line->description }}</div>
                                        @if($line->discount_rate > 0)
                                            <div class="text-xs text-green-400">Remise {{ $line->discount_rate }}% appliquée
                                            </div>
                                        @endif
                                    </td>
                                    <td class="p-4 text-right">{{ number_format($line->unit_price, 2, ',', ' ') }}</td>
                                    <td class="p-4 text-center">{{ $line->quantity }}</td>
                                    <td class="p-4 text-right font-bold">
                                        {{ number_format($line->total_amount, 2, ',', ' ') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-surface-dark border-t border-[#3a2e24]">
                            <tr>
                                <td colspan="3" class="p-4 text-right text-text-secondary">Total HT</td>
                                <td class="p-4 text-right font-bold text-white">
                                    {{ number_format($proposal->total_amount, 2, ',', ' ') }}</td>
                            </tr>
                            @if($proposal->discount_amount > 0)
                                <tr>
                                    <td colspan="3" class="p-4 text-right text-green-400">Remise Globale</td>
                                    <td class="p-4 text-right font-bold text-green-400">
                                        -{{ number_format($proposal->discount_amount, 2, ',', ' ') }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="3" class="p-4 text-right text-text-secondary">TVA
                                    ({{ number_format($proposal->lines->avg('tax_rate'), 0) }}%)</td>
                                <td class="p-4 text-right font-bold text-white">
                                    {{ number_format($proposal->tax_amount, 2, ',', ' ') }}</td>
                            </tr>
                            <tr class="text-lg bg-surface-highlight/30">
                                <td colspan="3" class="p-4 text-right font-bold text-white">Total TTC</td>
                                <td class="p-4 text-right font-bold text-primary">
                                    {{ number_format($proposal->total_amount_ttc, 2, ',', ' ') }} FCFA</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-ui.card>

            @if($proposal->terms)
                <x-ui.card title="Conditions & Modalités">
                    <div class="prose prose-invert max-w-none text-text-secondary">
                        {!! nl2br(e($proposal->terms)) !!}
                    </div>
                </x-ui.card>
            @endif
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <x-ui.card title="Informations Client">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="size-10 rounded-full bg-surface-highlight flex items-center justify-center text-primary font-bold">
                            {{ substr($proposal->contact->first_name, 0, 1) }}{{ substr($proposal->contact->last_name, 0, 1) }}
                        </div>
                        <div>
                            <div class="font-bold text-white">{{ $proposal->contact->display_name }}</div>
                            <div class="text-xs text-text-secondary">{{ $proposal->contact->email }}</div>
                        </div>
                    </div>

                    <div class="border-t border-[#3a2e24] my-2"></div>

                    <div class="text-sm">
                        <span class="text-text-secondary block mb-1">Adresse</span>
                        <span class="text-white">{{ $proposal->contact->address ?? 'Non renseignée' }}</span>
                    </div>
                    <div class="text-sm">
                        <span class="text-text-secondary block mb-1">Téléphone</span>
                        <span class="text-white">{{ $proposal->contact->phone ?? '-' }}</span>
                    </div>
                </div>
            </x-ui.card>

            @if($proposal->notes)
                <x-ui.card title="Notes Internes">
                    <p class="text-sm text-text-secondary italic">
                        {{ $proposal->notes }}
                    </p>
                </x-ui.card>
            @endif

            <x-ui.card title="Historique">
                <div class="border-l-2 border-[#3a2e24] ml-2 space-y-4 pl-4 py-2">
                    <div class="relative">
                        <div
                            class="absolute -left-[21px] top-1 size-3 bg-primary rounded-full border-2 border-surface-dark">
                        </div>
                        <p class="text-xs text-text-secondary">{{ $proposal->created_at->format('d/m/Y H:i') }}</p>
                        <p class="text-sm text-white">Devis créé par {{ $proposal->creator->name }}</p>
                    </div>
                    @if($proposal->sent_at)
                        <div class="relative">
                            <div
                                class="absolute -left-[21px] top-1 size-3 bg-blue-500 rounded-full border-2 border-surface-dark">
                            </div>
                            <p class="text-xs text-text-secondary">{{ $proposal->sent_at->format('d/m/Y H:i') }}</p>
                            <p class="text-sm text-white">Devis envoyé</p>
                        </div>
                    @endif
                    @if($proposal->accepted_at)
                        <div class="relative">
                            <div
                                class="absolute -left-[21px] top-1 size-3 bg-green-500 rounded-full border-2 border-surface-dark">
                            </div>
                            <p class="text-xs text-text-secondary">{{ $proposal->accepted_at->format('d/m/Y H:i') }}</p>
                            <p class="text-sm text-white">Devis accepté</p>
                        </div>
                    @endif
                </div>
            </x-ui.card>
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
                    Êtes-vous sûr de vouloir supprimer le devis <strong class="text-white">{{ $proposal->reference }}</strong> ?
                    <br><br>
                    <span class="text-xs">Montant: {{ number_format($proposal->total_amount_ttc, 0, ',', ' ') }} FCFA</span>
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