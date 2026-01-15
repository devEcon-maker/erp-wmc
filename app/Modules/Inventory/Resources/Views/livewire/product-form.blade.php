<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white">
                {{ $product->exists ? 'Modifier ' . $product->name : 'Nouveau Produit' }}
            </h2>
            <p class="text-text-secondary text-sm">Gestion du catalogue produits et services.</p>
        </div>
    </div>

    <form wire:submit="save">
        <x-ui.card>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Main Info -->
                <x-ui.select wire:model.live="type" label="Type" :error="$errors->first('type')">
                    <option value="product">Produit</option>
                    <option value="service">Service</option>
                </x-ui.select>

                <div class="flex items-center pt-6">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" wire:model="is_active"
                            class="rounded bg-background-dark border-[#3a2e24] text-primary focus:ring-primary">
                        <span class="text-sm font-medium text-text-secondary">Actif</span>
                    </label>
                </div>

                <x-ui.input wire:model="name" label="Nom" :error="$errors->first('name')" />
                <x-ui.input wire:model="reference" label="Référence" :error="$errors->first('reference')" />

                <x-ui.select wire:model="category_id" label="Catégorie" :error="$errors->first('category_id')">
                    <option value="">Aucune</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.input wire:model="unit" label="Unité (ex: pce, h, kg)" :error="$errors->first('unit')" />

                <!-- Description -->
                <x-ui.textarea wire:model="description" label="Description" :error="$errors->first('description')"
                    class="h-24 md:col-span-2" />

                <!-- Pricing -->
                <div class="md:col-span-2 border-t border-[#3a2e24] pt-4">
                    <h3 class="text-lg font-bold text-white mb-4">Prix & Marge</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-ui.input type="number" step="0.01" wire:model.live="purchase_price" label="Prix d'achat HT"
                            :error="$errors->first('purchase_price')" />
                        <x-ui.input type="number" step="0.01" wire:model.live="selling_price" label="Prix de vente HT"
                            :error="$errors->first('selling_price')" />
                        <x-ui.input type="number" step="0.01" wire:model="tax_rate" label="TVA (%)"
                            :error="$errors->first('tax_rate')" />
                    </div>

                    <!-- Margin Display -->
                    <div
                        class="bg-background-dark/50 border border-[#3a2e24] p-4 rounded-xl mt-4 flex justify-between items-center">
                        <div>
                            <span class="text-sm text-text-secondary block">Marge HT</span>
                            <span
                                class="text-xl font-bold {{ $this->margin >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                {{ number_format($this->margin, 2) }} FCFA
                            </span>
                        </div>
                        <div>
                            <span class="text-sm text-text-secondary block">Marge %</span>
                            @php
                                $mp = $this->marginPercent;
                                $color = $mp > 30 ? 'text-green-500' : ($mp >= 10 ? 'text-yellow-500' : 'text-red-500');
                            @endphp
                            <span class="text-xl font-bold {{ $color }}">
                                {{ number_format($mp, 1) }} %
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stock -->
                @if($type === 'product')
                    <div class="md:col-span-2 border-t border-[#3a2e24] pt-4">
                        <h3 class="text-lg font-bold text-white mb-4">Inventaire</h3>
                        <div class="flex items-center space-x-6">
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" wire:model.live="track_stock"
                                    class="rounded bg-background-dark border-[#3a2e24] text-primary focus:ring-primary">
                                <span class="text-sm font-medium text-text-secondary">Suivre le stock</span>
                            </label>

                            @if($track_stock)
                                <div class="w-48">
                                    <x-ui.input type="number" wire:model="min_stock_alert" label="Seuil d'alerte"
                                        :error="$errors->first('min_stock_alert')" />
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <x-ui.button type="secondary" href="{{ route('inventory.products.index') }}">
                    Annuler
                </x-ui.button>
                <x-ui.button type="primary" :submit="true" wire:loading.attr="disabled">
                    <span wire:loading.remove>Enregistrer</span>
                    <span wire:loading class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Enregistrement...
                    </span>
                </x-ui.button>
            </div>
        </x-ui.card>
    </form>
</div>