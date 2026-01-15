<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-start bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white">{{ $product->name }}</h2>
            <div class="mt-2 flex items-center space-x-2 text-sm text-text-secondary">
                <span class="font-mono bg-surface-highlight px-2 py-0.5 rounded text-primary font-bold">{{ $product->reference }}</span>
                <span>&bull;</span>
                <span>{{ ucfirst($product->type) }}</span>
                <span>&bull;</span>
                <span class="{{ $product->is_active ? 'text-green-500' : 'text-red-500' }}">
                    {{ $product->is_active ? 'Actif' : 'Inactif' }}
                </span>
            </div>
        </div>
        <div class="flex space-x-3">
            <x-ui.button href="{{ route('inventory.products.index') }}" type="secondary" class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                Retour
            </x-ui.button>
            <x-ui.button href="{{ route('inventory.products.edit', $product) }}" type="secondary" class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">edit</span>
                Modifier
            </x-ui.button>
            <x-ui.button wire:click="delete" wire:confirm="Supprimer ce produit ?" type="danger" class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">delete</span>
                Supprimer
            </x-ui.button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Info Principal -->
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card title="Informations Generales" class="bg-surface-dark border border-[#3a2e24]">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <span class="block text-sm text-text-secondary">Categorie</span>
                        <span class="font-medium text-white">{{ $product->category?->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-sm text-text-secondary">Unite</span>
                        <span class="font-medium text-white">{{ $product->unit }}</span>
                    </div>
                    <div class="md:col-span-2 px-4 py-3 bg-surface-highlight rounded-lg border border-[#3a2e24]">
                        <span class="block text-sm text-text-secondary mb-1">Description</span>
                        <p class="text-sm text-white whitespace-pre-wrap">{{ $product->description ?: '-' }}</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Niveaux de Stock" class="bg-surface-dark border border-[#3a2e24]">
                @if($product->track_stock)
                    <div class="text-center py-6 text-text-secondary">
                        <span class="material-symbols-outlined text-4xl mb-2 opacity-50">warehouse</span>
                        <p>Gestion multi-entrepots a venir (Phase 4).</p>
                        <p class="text-sm mt-2">Seuil d'alerte : <span class="text-primary font-bold">{{ $product->min_stock_alert }}</span></p>
                    </div>
                @else
                    <div class="text-center py-6 text-text-secondary">
                        <span class="material-symbols-outlined text-4xl mb-2 opacity-50">inventory_2</span>
                        <p>Ce produit n'est pas gere en stock.</p>
                    </div>
                @endif
            </x-ui.card>
        </div>

        <!-- Sidebar Pricing -->
        <div class="lg:col-span-1 space-y-6">
            <x-ui.card title="Tarification" class="bg-surface-dark border border-[#3a2e24]">
                <dl class="divide-y divide-[#3a2e24]">
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-text-secondary">Prix Achat HT</dt>
                        <dd class="text-sm text-white">{{ number_format($product->purchase_price, 2) }} FCFA</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-text-secondary">Marge HT</dt>
                        <dd class="text-sm {{ $product->margin >= 0 ? 'text-green-500' : 'text-red-500' }} font-bold">
                            {{ number_format($product->margin, 2) }} FCFA
                        </dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-text-secondary">Taux Marge</dt>
                        <dd class="text-sm font-bold text-white">
                            {{ number_format($product->margin_percent, 1) }} %
                        </dd>
                    </div>
                    <div class="py-3 flex justify-between border-t-2 border-[#3a2e24]">
                        <dt class="text-base font-bold text-white">Prix Vente HT</dt>
                        <dd class="text-base font-bold text-white">{{ number_format($product->selling_price, 2) }} FCFA</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm text-text-secondary">TVA ({{ $product->tax_rate }}%)</dt>
                        <dd class="text-sm text-white">
                            {{ number_format($product->selling_price * ($product->tax_rate / 100), 2) }} FCFA
                        </dd>
                    </div>
                    <div class="py-3 flex justify-between bg-primary/10 -mx-4 px-4 mt-2 rounded-lg">
                        <dt class="text-base font-bold text-primary">Prix TTC</dt>
                        <dd class="text-base font-bold text-primary">
                            {{ number_format($product->selling_price * (1 + $product->tax_rate / 100), 2) }} FCFA
                        </dd>
                    </div>
                </dl>
            </x-ui.card>
        </div>
    </div>
</div>
