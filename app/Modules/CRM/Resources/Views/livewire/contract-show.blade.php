<div class="space-y-6">
    <!-- Header -->
    <div
        class="flex flex-col md:flex-row justify-between items-start md:items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <h2 class="text-2xl font-bold text-white tracking-tight">
                    {{ $contract->reference }}
                </h2>
                @php
                    $statusColors = [
                        'draft' => 'gray',
                        'active' => 'green',
                        'suspended' => 'orange',
                        'terminated' => 'red',
                    ];
                    $statusLabels = [
                        'draft' => 'Brouillon',
                        'active' => 'Actif',
                        'suspended' => 'Suspendu',
                        'terminated' => 'Terminé',
                    ];
                @endphp
                <x-ui.badge :color="$statusColors[$contract->status] ?? 'gray'">
                    {{ $statusLabels[$contract->status] ?? ucfirst($contract->status) }}
                </x-ui.badge>
                @if($contract->type === 'subscription')
                    <x-ui.badge color="purple" icon="autorenew">Abonnement</x-ui.badge>
                @endif
            </div>
            <p class="text-text-secondary text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-[16px]">business</span>
                {{ $contract->contact->display_name }}
            </p>
        </div>

        <div class="flex gap-3">
            <x-ui.button href="{{ route('crm.contracts.edit', $contract) }}" type="secondary" icon="edit">
                Modifier
            </x-ui.button>
            <x-ui.button type="secondary" icon="print">
                PDF
            </x-ui.button>

            @if($contract->status === 'draft')
                <x-ui.button wire:click="activate" type="primary" icon="play_circle">
                    Activer
                </x-ui.button>
            @elseif($contract->status === 'active')
                <x-ui.button wire:click="suspend" type="warning" icon="pause_circle">
                    Suspendre
                </x-ui.button>
                <x-ui.button wire:click="terminate" type="danger" icon="stop_circle">
                    Résilier
                </x-ui.button>
            @elseif($contract->status === 'suspended')
                <x-ui.button wire:click="activate" type="primary" icon="play_circle">
                    Réactiver
                </x-ui.button>
            @endif
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
                            @foreach($contract->lines as $line)
                                <tr class="text-white hover:bg-surface-highlight/20">
                                    <td class="p-4">
                                        <div class="font-bold">{{ $line->description }}</div>
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
                                <td colspan="3" class="p-4 text-right text-text-secondary">Total HT / Période</td>
                                <td class="p-4 text-right font-bold text-white">
                                    {{ number_format($contract->total_amount, 2, ',', ' ') }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="p-4 text-right text-text-secondary">TVA</td>
                                <td class="p-4 text-right font-bold text-white">
                                    {{ number_format($contract->tax_amount, 2, ',', ' ') }}</td>
                            </tr>
                            <tr class="text-lg bg-surface-highlight/30">
                                <td colspan="3" class="p-4 text-right font-bold text-white">Total TTC</td>
                                <td class="p-4 text-right font-bold text-primary">
                                    {{ number_format($contract->total_amount_ttc, 2, ',', ' ') }} FCFA</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-ui.card>

            @if($contract->terms)
                <x-ui.card title="Conditions Particulières">
                    <p class="text-sm text-white whitespace-pre-line">
                        {{ $contract->terms }}
                    </p>
                </x-ui.card>
            @endif
            @if($contract->notes)
                <x-ui.card title="Notes Internes">
                    <p class="text-sm text-text-secondary italic">
                        {{ $contract->notes }}
                    </p>
                </x-ui.card>
            @endif
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <x-ui.card title="Période & Facturation">
                <div class="space-y-4">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-text-secondary">Date de début</span>
                        <span class="text-white font-bold">{{ $contract->start_date->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-text-secondary">Date de fin</span>
                        <span
                            class="text-white font-bold">{{ $contract->end_date ? $contract->end_date->format('d/m/Y') : 'Indéterminée' }}</span>
                    </div>

                    <div class="border-t border-[#3a2e24]"></div>

                    @if($contract->type === 'subscription')
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-text-secondary">Fréquence</span>
                            <span
                                class="text-white">{{ match ($contract->billing_frequency) { 'monthly' => 'Mensuelle', 'quarterly' => 'Trimestrielle', 'yearly' => 'Annuelle'} }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-text-secondary">Prochaine facturation</span>
                            <span
                                class="text-primary font-bold">{{ $contract->next_billing_date ? $contract->next_billing_date->format('d/m/Y') : 'À l\'activation' }}</span>
                        </div>
                    @else
                        <div class="text-sm text-text-secondary text-center italic">Facturation unique</div>
                    @endif
                </div>
            </x-ui.card>

            <x-ui.card title="Informations Client">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="size-10 rounded-full bg-surface-highlight flex items-center justify-center text-primary font-bold">
                            {{ substr($contract->contact->first_name, 0, 1) }}{{ substr($contract->contact->last_name, 0, 1) }}
                        </div>
                        <div>
                            <div class="font-bold text-white">{{ $contract->contact->display_name }}</div>
                            <div class="text-xs text-text-secondary">{{ $contract->contact->email }}</div>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</div>