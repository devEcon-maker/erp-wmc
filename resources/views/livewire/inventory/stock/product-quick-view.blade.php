<div>
    <div x-data="{ show: false, name: 'product-quick-view' }" x-show="show"
        x-on:open-modal.window="if ($event.detail.name === name) show = true"
        x-on:open-quick-view.window="$wire.openQuickView($event.detail)"
        x-on:close-modal.window="show = false"
        x-on:keydown.escape.window="show = false" style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0">

        <!-- Backdrop -->
        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 transform transition-all" x-on:click="show = false">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
        </div>

        <!-- Content -->
        <div x-show="show" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative mb-6 bg-surface-dark border border-[#3a2e24] rounded-2xl overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-4xl sm:mx-auto">

            @if(isset($product) && $product)
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-white">{{ $product->name }}</h3>
                            <div class="mt-2 flex items-center gap-2 text-sm text-text-secondary">
                                <span
                                    class="font-mono bg-surface-highlight px-2 py-0.5 rounded text-primary font-bold">{{ $product->reference }}</span>
                                <span>&bull;</span>
                                <span>{{ $product->category?->name ?? 'Sans catégorie' }}</span>
                                <span>&bull;</span>
                                <span class="{{ $product->is_active ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $product->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>
                        </div>
                        <button x-on:click="show = false" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Fermer</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Grid Layout -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <!-- Main Info -->
                        <div class="md:col-span-2 space-y-6">
                            <!-- Description -->
                            <div class="bg-surface-highlight/50 rounded-xl p-4 border border-[#3a2e24]">
                                <h4 class="text-sm font-bold text-text-secondary uppercase tracking-wider mb-2">Description
                                </h4>
                                <p class="text-sm text-white whitespace-pre-wrap">
                                    {{ $product->description ?: 'Aucune description disponible.' }}
                                </p>
                            </div>

                            <!-- Stock Stats -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-surface-highlight/50 rounded-xl p-4 border border-[#3a2e24]">
                                    <h4 class="text-sm font-bold text-text-secondary uppercase tracking-wider mb-1">Stock
                                        Global</h4>
                                    <p class="text-2xl font-bold text-white">
                                        {{ $product->current_stock }} {{ $product->unit }}
                                    </p>
                                </div>
                                <div class="bg-surface-highlight/50 rounded-xl p-4 border border-[#3a2e24]">
                                    <h4 class="text-sm font-bold text-text-secondary uppercase tracking-wider mb-1">Seuil
                                        Alerte</h4>
                                    <p class="text-2xl font-bold text-primary">
                                        {{ $product->min_stock_alert }} {{ $product->unit }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar: Pricing -->
                        <div class="md:col-span-1">
                            <div class="bg-surface-highlight/30 rounded-xl p-4 border border-[#3a2e24]">
                                <h4 class="text-sm font-bold text-text-secondary uppercase tracking-wider mb-4">Tarification
                                </h4>
                                <dl class="space-y-3 divide-y divide-[#3a2e24]">
                                    <div class="flex justify-between pb-3">
                                        <dt class="text-sm text-text-secondary">Prix Achat</dt>
                                        <dd class="text-sm font-medium text-white">
                                            {{ number_format($product->purchase_price, 0, ',', ' ') }} <span
                                                class="text-xs text-text-secondary">FCFA</span>
                                        </dd>
                                    </div>
                                    <div class="flex justify-between py-3">
                                        <dt class="text-sm text-text-secondary">Prix Vente HT</dt>
                                        <dd class="text-sm font-bold text-white">
                                            {{ number_format($product->selling_price, 0, ',', ' ') }} <span
                                                class="text-xs text-text-secondary">FCFA</span>
                                        </dd>
                                    </div>
                                    <div class="flex justify-between py-3 border-t border-[#3a2e24]">
                                        <dt class="text-base font-bold text-primary">Prix TTC</dt>
                                        <dd class="text-base font-bold text-primary">
                                            {{ number_format($product->selling_price * (1 + $product->tax_rate / 100), 0, ',', ' ') }}
                                            <span class="text-xs">FCFA</span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="mt-4 flex justify-end">
                                <a href="{{ route('inventory.products.edit', $product->id) }}"
                                    class="text-sm text-primary hover:text-primary-hover hover:underline">
                                    Modifier le produit &rarr;
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary"></div>
                    <p class="mt-2 text-text-secondary">Chargement...</p>
                </div>
            @endif
        </div>
    </div>
</div>