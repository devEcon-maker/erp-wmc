<div>
    <!-- Header -->
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestion des Stocks</h1>
            <p class="text-text-secondary text-sm">Suivi des niveaux de stock et alertes.</p>
        </div>
        <div class="flex gap-2">
            <button x-data x-on:click="$dispatch('open-modal', { name: 'stock-movement-form' })"
                class="bg-primary text-white px-4 py-2 rounded-xl hover:bg-primary-hover transition-colors shadow-lg shadow-primary/20 font-bold">
                Mouvement Manuel
            </button>
        </div>
    </div>

    <livewire:inventory.stock.stock-movement-form />

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
            <h3 class="text-text-secondary text-sm font-bold uppercase tracking-wider">Produits en Alerte</h3>
            <p class="text-3xl font-bold text-red-500 mt-2">{{ $stats['alerts'] }}</p>
        </x-ui.card>
        <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
            <h3 class="text-text-secondary text-sm font-bold uppercase tracking-wider">Mouvements Aujourd'hui</h3>
            <p class="text-3xl font-bold text-blue-500 mt-2">{{ $stats['movements'] }}</p>
        </x-ui.card>
        <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
            <h3 class="text-text-secondary text-sm font-bold uppercase tracking-wider">Valeur du Stock (Estimée)</h3>
            <p class="text-3xl font-bold text-green-500 mt-2">- FCFA</p>
        </x-ui.card>
    </div>

    <!-- Filters -->
    <div class="bg-surface-dark border border-[#3a2e24] p-4 rounded-2xl mb-6 flex flex-wrap gap-4 items-end">
        <div class="w-full md:w-1/4">
            <x-ui.select label="Entrepôt" :options="$warehouses->pluck('name', 'id')" wire:model.live="warehouseId" />
        </div>
        <div class="w-full md:w-1/3">
            <x-ui.input label="Recherche" placeholder="Référence, Nom..." wire:model.live.debounce.300ms="search" />
        </div>
        <div class="flex items-center gap-2 pb-2">
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" wire:model.live="showAlertsOnly"
                    class="rounded bg-background-dark border-[#3a2e24] text-primary focus:ring-primary">
                <span class="text-sm font-medium text-text-secondary">Alertes uniquement</span>
            </label>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#3a2e24]">
                <thead class="bg-surface-highlight">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Référence</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Produit</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Stock Physique</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Réservé</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Disponible</th>
                        <th
                            class="px-6 py-3 text-center text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#3a2e24]">
                    @forelse($products as $product)
                        @php
                            $level = $product->stockLevels->first();
                            $qty = $level ? $level->quantity : 0;
                            $reserved = $level ? $level->reserved_quantity : 0;
                            $available = $qty - $reserved;
                            $isAlert = $qty <= $product->min_stock_alert;
                        @endphp
                        <tr class="hover:bg-surface-highlight/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">{{ $product->reference }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">{{ $product->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-white">{{ $qty }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-text-secondary">{{ $reserved }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold {{ $available <= 0 ? 'text-red-500' : 'text-green-500' }}">
                                {{ $available }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($isAlert)
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-400/10 text-red-500 border border-red-400/20">
                                        Alerte
                                    </span>
                                @else
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-400/10 text-green-500 border border-green-400/20">
                                        OK
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button
                                    wire:click="$dispatch('openModal', { component: 'inventory.product-show', arguments: { product: {{ $product->id }} } })"
                                    class="text-primary hover:text-primary-hover font-bold transition-colors">Voir</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-text-secondary">Aucun produit trouvé</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="p-4 border-t border-[#3a2e24]">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>