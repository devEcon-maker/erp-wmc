<div class="space-y-6">
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white">Produits & Services</h2>
            <p class="text-text-secondary text-sm">Gestion du catalogue et des prix.</p>
        </div>
        <x-ui.button href="{{ route('inventory.products.create') }}" type="primary"
            class="flex items-center gap-2 shadow-lg shadow-primary/20">
            <span class="material-symbols-outlined text-[20px]">add_circle</span>
            Nouveau
        </x-ui.button>
    </div>

    <!-- Filters -->
    <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-ui.input wire:model.live.debounce.300ms="search" label="Recherche" placeholder="Réf, Nom..." />

            <x-ui.select wire:model.live="type" label="Type">
                <option value="">Tous</option>
                <option value="product">Produit</option>
                <option value="service">Service</option>
            </x-ui.select>

            <x-ui.select wire:model.live="category_id" label="Catégorie">
                <option value="">Toutes</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select wire:model.live="is_active" label="Statut">
                <option value="">Tous</option>
                <option value="1">Actif</option>
                <option value="0">Inactif</option>
            </x-ui.select>
        </div>
    </x-ui.card>

    <!-- Table -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#3a2e24]">
                <thead class="bg-surface-highlight">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Référence</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Catégorie</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Prix Achat</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Prix Vente</th>
                        <th
                            class="px-6 py-3 text-center text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Marge</th>
                        <th
                            class="px-6 py-3 text-center text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#3a2e24]">
                    @forelse($products as $product)
                        <tr class="hover:bg-surface-highlight/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">{{ $product->reference }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">
                                <span class="font-bold text-white block">{{ $product->name }}</span>
                                <span class="text-xs opacity-70">{{ ucfirst($product->type) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">
                                {{ $product->category?->name ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-text-secondary">
                                {{ number_format($product->purchase_price, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-white">
                                {{ number_format($product->selling_price, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $margin = $product->margin_percent;
                                    $color = $margin > 30 ? 'green' : ($margin >= 10 ? 'yellow' : 'red');
                                @endphp
                                <x-ui.badge :color="$color">
                                    {{ number_format($margin, 1) }}%
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button wire:click="toggleActive({{ $product->id }})"
                                    class="focus:outline-none transition-transform hover:scale-105">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $product->is_active ? 'bg-green-500/10 text-green-500 border-green-500/20' : 'bg-red-500/10 text-red-500 border-red-500/20' }}">
                                        {{ $product->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('inventory.products.show', $product) }}"
                                        class="p-2 rounded-lg text-text-secondary hover:text-primary hover:bg-primary/10 transition-colors"
                                        title="Voir">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </a>
                                    <a href="{{ route('inventory.products.edit', $product) }}"
                                        class="p-2 rounded-lg text-text-secondary hover:text-yellow-500 hover:bg-yellow-500/10 transition-colors"
                                        title="Modifier">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </a>
                                    <button wire:click="duplicate({{ $product->id }})"
                                        class="p-2 rounded-lg text-text-secondary hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors"
                                        title="Dupliquer">
                                        <span class="material-symbols-outlined text-[20px]">content_copy</span>
                                    </button>
                                    <button wire:click="confirmDelete({{ $product->id }})"
                                        class="p-2 rounded-lg text-text-secondary hover:text-red-500 hover:bg-red-500/10 transition-colors"
                                        title="Supprimer">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-text-secondary">Aucun produit trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-[#3a2e24]">
            {{ $products->links() }}
        </div>
    </div>
</div>