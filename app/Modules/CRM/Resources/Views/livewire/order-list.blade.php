<div class="space-y-6">
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">
                Commandes Clients
            </h2>
            <p class="text-text-secondary text-sm">Suivi des commandes et livraisons.</p>
        </div>
        <div class="flex space-x-3">
            <x-ui.button href="{{ route('crm.orders.create') }}" type="primary" icon="add">
                Nouvelle Commande
            </x-ui.button>
        </div>
    </div>

    <!-- Filters -->
    <x-ui.card>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-ui.input wire:model.live.debounce.300ms="search" label="Recherche" placeholder="Référence, Client..." />

            <x-ui.select wire:model.live="status" label="Statut">
                <option value="">Tous les statuts</option>
                <option value="draft">Brouillon</option>
                <option value="confirmed">Confirmée</option>
                <option value="processing">En traitement</option>
                <option value="shipped">Expédiée</option>
                <option value="delivered">Livrée</option>
                <option value="cancelled">Annulée</option>
            </x-ui.select>
        </div>
    </x-ui.card>

    <!-- Table -->
    <x-ui.card class="overflow-hidden">
        <x-ui.table :headers="['Référence', 'Client', 'Date', 'Montant TTC', 'Statut', 'Actions']">
            @forelse($orders as $order)
                <tr class="hover:bg-surface-highlight/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-white">{{ $order->reference }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-text-secondary">{{ $order->contact->display_name }}</div>
                        @if($order->contact->company_name)
                            <div class="text-xs text-text-secondary opacity-70">{{ $order->contact->company_name }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-text-secondary">
                        {{ $order->order_date->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-white">{{ number_format($order->total_amount_ttc, 0, ',', ' ') }}
                            FCFA</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
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
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('crm.orders.show', $order) }}"
                                class="p-2 rounded-lg hover:bg-surface-highlight text-text-secondary hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                            </a>
                            <a href="{{ route('crm.orders.edit', $order) }}"
                                class="p-2 rounded-lg hover:bg-surface-highlight text-text-secondary hover:text-orange-400 transition-colors">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-text-secondary">
                        <div class="flex flex-col items-center justify-center">
                            <span class="material-symbols-outlined text-4xl mb-2 opacity-50">shopping_cart</span>
                            <p>Aucune commande trouvée.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-ui.table>
        <div class="border-t border-[#3a2e24] p-4">
            {{ $orders->links() }}
        </div>
    </x-ui.card>
</div>