<x-ui.modal name="stock-movement-form">
    <div class="p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-white">Nouveau Mouvement de Stock</h3>
            <button x-on:click="$dispatch('close-modal')" class="text-gray-400 hover:text-gray-500">
                <span class="sr-only">Fermer</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form wire:submit.prevent="save">
            <div class="grid grid-cols-1 gap-4">
                <!-- Type -->
                <div>
                    <x-ui.select label="Type de Mouvement" :options="['in' => 'Entrée', 'out' => 'Sortie', 'transfer' => 'Transfert', 'adjustment' => 'Ajustement Inventaire']" wire:model.live="type" />
                </div>

                <!-- Product -->
                <div>
                    <x-ui.select label="Produit" :options="$products->pluck('name', 'id')" wire:model.live="product_id"
                        placeholder="Sélectionner un produit..." />
                </div>

                <!-- Warehouse (Source) -->
                <div>
                    <x-ui.select label="{{ $type === 'transfer' ? 'Entrepôt Source' : 'Entrepôt' }}"
                        :options="$warehouses->pluck('name', 'id')" wire:model.live="warehouse_id" />
                </div>

                <!-- Warehouse (Dest) for Transfer -->
                @if($type === 'transfer')
                    <div>
                        <x-ui.select label="Entrepôt Destination" :options="$warehouses->pluck('name', 'id')"
                            wire:model.live="to_warehouse_id" />
                    </div>
                @endif

                <!-- Quantity -->
                <div>
                    @if($type === 'adjustment' && $product_id && $warehouse_id)
                        <div class="mb-2 text-sm text-text-secondary">
                            Stock actuel : <strong>{{ $current_stock }}</strong>
                        </div>
                    @endif

                    <x-ui.input type="number" step="0.001"
                        label="{{ $type === 'adjustment' ? 'Nouveau Stock réel' : 'Quantité' }}"
                        wire:model="quantity" />
                </div>

                <!-- Notes -->
                <div>
                    <x-ui.textarea label="Notes / Raison" wire:model="notes" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close-modal')"
                    class="px-4 py-2 border border-[#3a2e24] rounded-md shadow-sm text-sm font-medium text-text-secondary bg-transparent hover:text-white hover:bg-surface-highlight transition-colors">
                    Annuler
                </button>
                <button type="submit"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-hover transition-colors">
                    Valider
                </button>
            </div>
        </form>
    </div>
</x-ui.modal>