<div class="space-y-6">
    <!-- Header -->
    <div
        class="flex flex-col md:flex-row justify-between items-start md:items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <h2 class="text-2xl font-bold text-white tracking-tight">
                    Commande {{ $order->reference }}
                </h2>
                @php
                    $statusColors = [
                        'draft' => 'gray',
                        'confirmed' => 'blue',
                        'processing' => 'orange',
                        'shipped' => 'purple',
                        'delivered' => 'green',
                        'cancelled' => 'red',
                    ];
                    $statusLabels = [
                        'draft' => 'Brouillon',
                        'confirmed' => 'Confirmée',
                        'processing' => 'En traitement',
                        'shipped' => 'Expédiée',
                        'delivered' => 'Livrée',
                        'cancelled' => 'Annulée',
                    ];
                @endphp
                <x-ui.badge :color="$statusColors[$order->status] ?? 'gray'">
                    {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                </x-ui.badge>
            </div>
            <p class="text-text-secondary text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-[16px]">business</span>
                {{ $order->contact->display_name }}
                <span class="text-[#3a2e24]">|</span>
                <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                {{ $order->order_date->format('d/m/Y') }}
            </p>
        </div>

        <div class="flex gap-3">
            <x-ui.button href="{{ route('crm.orders.edit', $order) }}" type="secondary" icon="edit">
                Modifier
            </x-ui.button>
            <x-ui.button href="{{ route('crm.orders.pdf', $order) }}" type="secondary" icon="print">
                Imprimer PDF
            </x-ui.button>

            @if($order->status === 'draft')
                <x-ui.button wire:click="confirmOrder" type="primary" icon="check_circle">
                    Confirmer
                </x-ui.button>
            @elseif($order->status === 'confirmed')
                <x-ui.button wire:click="markAsDelivered" type="success" icon="local_shipping">
                    Marquer Livrée
                </x-ui.button>
            @endif

            @if(!in_array($order->status, ['cancelled', 'delivered']))
                <x-ui.button wire:click="cancelOrder" type="danger" icon="cancel">
                    Annuler
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
                            @foreach($order->lines as $line)
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
                                    {{ number_format($order->total_amount, 2, ',', ' ') }}</td>
                            </tr>
                            @if($order->discount_amount > 0)
                                <tr>
                                    <td colspan="3" class="p-4 text-right text-green-400">Remise Globale</td>
                                    <td class="p-4 text-right font-bold text-green-400">
                                        -{{ number_format($order->discount_amount, 2, ',', ' ') }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="3" class="p-4 text-right text-text-secondary">TVA
                                    ({{ number_format($order->lines->avg('tax_rate'), 0) }}%)</td>
                                <td class="p-4 text-right font-bold text-white">
                                    {{ number_format($order->tax_amount, 2, ',', ' ') }}</td>
                            </tr>
                            <tr class="text-lg bg-surface-highlight/30">
                                <td colspan="3" class="p-4 text-right font-bold text-white">Total TTC</td>
                                <td class="p-4 text-right font-bold text-primary">
                                    {{ number_format($order->total_amount_ttc, 2, ',', ' ') }} FCFA</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-ui.card>

            @if($order->notes)
                <x-ui.card title="Notes Internes">
                    <p class="text-sm text-text-secondary italic">
                        {{ $order->notes }}
                    </p>
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
                            {{ substr($order->contact->first_name, 0, 1) }}{{ substr($order->contact->last_name, 0, 1) }}
                        </div>
                        <div>
                            <div class="font-bold text-white">{{ $order->contact->display_name }}</div>
                            <div class="text-xs text-text-secondary">{{ $order->contact->email }}</div>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Livraison">
                <div class="space-y-4">
                    <div class="text-sm">
                        <span class="text-text-secondary block mb-1">Date prévue</span>
                        <span
                            class="text-white font-bold">{{ $order->delivery_date ? $order->delivery_date->format('d/m/Y') : 'Non définie' }}</span>
                    </div>
                    <div class="border-t border-[#3a2e24]"></div>
                    <div class="text-sm">
                        <span class="text-text-secondary block mb-1">Adresse de livraison</span>
                        <span class="text-white">{{ $order->shipping_address ?? 'Identique adresse contact' }}</span>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Historique">
                <div class="border-l-2 border-[#3a2e24] ml-2 space-y-4 pl-4 py-2">
                    <div class="relative">
                        <div
                            class="absolute -left-[21px] top-1 size-3 bg-primary rounded-full border-2 border-surface-dark">
                        </div>
                        <p class="text-xs text-text-secondary">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                        <p class="text-sm text-white">Commande créée par {{ $order->creator->name }}</p>
                    </div>
                    @if($order->status === 'confirmed')
                        <div class="relative">
                            <div
                                class="absolute -left-[21px] top-1 size-3 bg-blue-500 rounded-full border-2 border-surface-dark">
                            </div>
                            <p class="text-sm text-white">Commande confirmée</p>
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>
</div>