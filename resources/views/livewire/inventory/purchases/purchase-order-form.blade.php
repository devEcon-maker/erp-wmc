<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white">
                {{ $purchaseOrder ? 'Modifier Commande ' . $purchaseOrder->reference : 'Nouvelle Commande Fournisseur' }}
            </h2>
            <p class="text-text-secondary text-sm">Gestion des achats et approvisionnements.</p>
        </div>
    </div>

    <form wire:submit.prevent="save" class="space-y-6">
        <x-ui.card>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Supplier -->
                <x-ui.select label="Fournisseur" :options="$suppliers->pluck('company_name', 'id')"
                    wire:model.live="supplier_id" placeholder="Sélectionner..."
                    :error="$errors->first('supplier_id')" />

                <!-- Destinataire (Approbateur) -->
                <x-ui.select label="Adresser la demande à" :options="$approvers->pluck('name', 'id')"
                    wire:model="assigned_to" placeholder="Sélectionner le destinataire..."
                    :error="$errors->first('assigned_to')" />

                <!-- Dates -->
                <div class="grid grid-cols-2 gap-4">
                    <x-ui.input type="date" label="Date" wire:model="date" :error="$errors->first('date')" />
                    <x-ui.input type="date" label="Date d'utilisation" wire:model="expected_date"
                        :error="$errors->first('expected_date')" />
                </div>
            </div>

            <div class="mt-4">
                <x-ui.textarea label="Notes" wire:model="notes" rows="2" placeholder="Notes internes..." />
            </div>
        </x-ui.card>

        <!-- Lines -->
        <x-ui.card title="Lignes de commande" class="overflow-visible">
            <div class="space-y-4">
                <div
                    class="hidden md:grid grid-cols-12 gap-2 text-sm font-bold text-text-secondary uppercase px-2 mb-2">
                    <div class="col-span-4">Produit</div>
                    <div class="col-span-3">Description</div>
                    <div class="col-span-1 text-center">Qté</div>
                    <div class="col-span-1 text-right">P.U. HT</div>
                    <div class="col-span-1 text-center">TVA %</div>
                    <div class="col-span-1 text-right">Total</div>
                    <div class="col-span-1"></div>
                </div>

                @foreach($lines as $index => $line)
                    <div
                        class="grid grid-cols-1 md:grid-cols-12 gap-2 items-start bg-background-dark/50 p-2 rounded-lg border border-[#3a2e24]">
                        <!-- Product -->
                        <div class="col-span-4">
                            <select wire:model.live="lines.{{ $index }}.product_id"
                                class="w-full bg-background-dark border border-[#3a2e24] text-white rounded-lg p-2 text-sm focus:ring-1 focus:ring-primary outline-none">
                                <option value="">Sélectionner...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                            @error('lines.' . $index . '.product_id') <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="col-span-3">
                            <input type="text" wire:model="lines.{{ $index }}.description" placeholder="Description"
                                class="w-full bg-transparent border-b border-[#3a2e24] text-white text-sm p-1 focus:border-primary outline-none" />
                        </div>

                        <!-- Qty -->
                        <div class="col-span-1">
                            <input type="number" step="0.001" wire:model.live="lines.{{ $index }}.quantity"
                                class="w-full bg-background-dark border border-[#3a2e24] text-white rounded-lg p-2 text-sm text-center focus:ring-1 focus:ring-primary outline-none" />
                        </div>

                        <!-- Price -->
                        <div class="col-span-1">
                            <input type="number" step="0.01" wire:model.live="lines.{{ $index }}.unit_price"
                                class="w-full bg-background-dark border border-[#3a2e24] text-white rounded-lg p-2 text-sm text-right focus:ring-1 focus:ring-primary outline-none" />
                        </div>

                        <!-- Tax -->
                        <div class="col-span-1">
                            <input type="number" step="0.01" wire:model="lines.{{ $index }}.tax_rate"
                                class="w-full bg-background-dark border border-[#3a2e24] text-white rounded-lg p-2 text-sm text-center focus:ring-1 focus:ring-primary outline-none" />
                        </div>

                        <!-- Total Line -->
                        <div class="col-span-1 text-right py-2 px-2 text-white font-bold text-sm">
                            {{ number_format(floatval($line['quantity'] ?? 0) * floatval($line['unit_price'] ?? 0), 2, ',', ' ') }}
                        </div>

                        <!-- Actions -->
                        <div class="col-span-1 text-right">
                            <button type="button" wire:click="removeLine({{ $index }})"
                                class="p-2 text-red-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </div>
                    </div>
                @endforeach

                <button type="button" wire:click="addLine"
                    class="mt-4 flex items-center gap-2 text-primary font-bold hover:text-primary/80 transition-colors">
                    <span class="material-symbols-outlined">add_circle</span>
                    Ajouter une ligne
                </button>
            </div>
        </x-ui.card>

        <div class="flex justify-end gap-4 border-t border-[#3a2e24] pt-6">
            <x-ui.button type="secondary" href="{{ route('inventory.purchases.index') }}">Annuler</x-ui.button>
            <x-ui.button type="primary" wire:loading.attr="disabled">Enregistrer</x-ui.button>
        </div>
    </form>
</div>