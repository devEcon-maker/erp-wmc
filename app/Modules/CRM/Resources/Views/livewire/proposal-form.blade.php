<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white">
                {{ $proposal->exists ? 'Modifier le devis ' . $proposal->reference : 'Nouveau Devis' }}
            </h2>
            <p class="text-text-secondary text-sm">Éditeur de proposition commerciale.</p>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        <!-- Erreurs de validation globales -->
        @if ($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4">
                <div class="flex items-center gap-2 text-red-400 mb-2">
                    <span class="material-symbols-outlined">error</span>
                    <span class="font-bold">Veuillez corriger les erreurs suivantes :</span>
                </div>
                <ul class="list-disc list-inside text-red-400 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Settings -->
        <x-ui.card title="Informations Générales">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Client -->
                <x-ui.select label="Client *" wire:model="contact_id"
                    :options="['' => 'Sélectionner un client...'] + $contacts->pluck('display_name', 'id')->toArray()"
                    :error="$errors->first('contact_id')" />

                <!-- Date -->
                <x-ui.input type="date" label="Valable jusqu'au" wire:model="valid_until" 
                    :error="$errors->first('valid_until')" />
            </div>
            
            <div class="mt-4">
                <x-ui.textarea label="Notes (Interne)" wire:model="notes" rows="2" />
            </div>
        </x-ui.card>

        <!-- Product Lines -->
        <x-ui.card title="Lignes de Produits & Services" class="overflow-visible">
            <div class="space-y-4">
                <div class="hidden md:grid grid-cols-12 gap-2 text-sm font-bold text-text-secondary uppercase px-2 mb-2">
                    <div class="col-span-4">Produit / Description</div>
                    <div class="col-span-2 text-right">Prix Unitaire</div>
                    <div class="col-span-1 text-center">Qté</div>
                    <div class="col-span-1 text-center">TVA %</div>
                    <div class="col-span-1 text-center">Remise %</div>
                    <div class="col-span-2 text-right">Total HT</div>
                    <div class="col-span-1"></div>
                </div>

                @foreach($lines as $index => $line)
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-start bg-background-dark/50 p-2 rounded-lg border border-[#3a2e24]">
                        <!-- Product/Desc -->
                        <div class="col-span-4 space-y-2">
                            <select wire:model.live="lines.{{ $index }}.product_id"
                                class="w-full bg-background-dark border border-[#3a2e24] text-white rounded-lg p-2 text-sm focus:ring-1 focus:ring-primary outline-none">
                                <option value="">Sélectionner un produit...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                            <input type="text" wire:model="lines.{{ $index }}.description" placeholder="Description"
                                class="w-full bg-transparent border-b border-[#3a2e24] text-white text-sm p-1 focus:border-primary outline-none" />
                            @error('lines.'.$index.'.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Price -->
                        <div class="col-span-2">
                            <input type="number" step="0.01" wire:model.live.debounce.500ms="lines.{{ $index }}.unit_price"
                                class="w-full bg-background-dark border border-[#3a2e24] text-white rounded-lg p-2 text-sm text-right focus:ring-1 focus:ring-primary outline-none" />
                        </div>

                        <!-- Qty -->
                        <div class="col-span-1">
                            <input type="number" step="1" wire:model.live.debounce.300ms="lines.{{ $index }}.quantity"
                                class="w-full bg-background-dark border border-[#3a2e24] text-white rounded-lg p-2 text-sm text-center focus:ring-1 focus:ring-primary outline-none" />
                        </div>

                        <!-- Tax -->
                        <div class="col-span-1">
                            <input type="number" step="0.01" wire:model.live.debounce.500ms="lines.{{ $index }}.tax_rate"
                                class="w-full bg-background-dark border border-[#3a2e24] text-white rounded-lg p-2 text-sm text-center focus:ring-1 focus:ring-primary outline-none" />
                        </div>

                        <!-- Discount -->
                        <div class="col-span-1">
                            <input type="number" step="0.01" wire:model.live.debounce.500ms="lines.{{ $index }}.discount_rate"
                                class="w-full bg-background-dark border border-[#3a2e24] text-white rounded-lg p-2 text-sm text-center focus:ring-1 focus:ring-primary outline-none" />
                        </div>

                        <!-- Total Line (Calculated) -->
                        <div class="col-span-2 text-right py-2 px-2 text-white font-bold text-sm">
                            @php
                                $qty = floatval($line['quantity'] ?? 0);
                                $price = floatval($line['unit_price'] ?? 0);
                                $discount = floatval($line['discount_rate'] ?? 0);
                                $total = $qty * $price * (1 - ($discount / 100));
                            @endphp
                            {{ number_format($total, 2, ',', ' ') }}
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

                <button type="button" wire:click="addLine" class="mt-4 flex items-center gap-2 text-primary font-bold hover:text-primary/80 transition-colors">
                    <span class="material-symbols-outlined">add_circle</span>
                    Ajouter une ligne
                </button>
            </div>
        </x-ui.card>

        <!-- Terms & Totals -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8">
                <x-ui.card title="Conditions">
                    <x-ui.textarea label="Conditions Commerciales" wire:model="terms" rows="6" placeholder="Modalités de paiement, validité, etc..." />
                </x-ui.card>
            </div>
            
            <div class="lg:col-span-4">
                <x-ui.card>
                    <div class="space-y-4">
                        <div class="flex justify-between text-text-secondary">
                            <span>Total HT</span>
                            <span class="font-bold text-white">{{ number_format($total_amount, 2, ',', ' ') }} FCFA</span>
                        </div>
                        @if($discount_amount > 0)
                        <div class="flex justify-between text-green-400">
                            <span>Remise Total</span>
                            <span class="font-bold">-{{ number_format($discount_amount, 2, ',', ' ') }} FCFA</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-text-secondary">
                            <span>TVA Total</span>
                            <span class="font-bold text-white">{{ number_format($tax_amount, 2, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="border-t border-[#3a2e24] pt-4 mt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-bold text-white">Total TTC</span>
                                <span class="text-xl font-bold text-primary">{{ number_format($total_amount_ttc, 2, ',', ' ') }} FCFA</span>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>

        <div class="flex justify-end gap-4 border-t border-[#3a2e24] pt-6">
            <x-ui.button type="secondary" href="{{ route('crm.proposals.index') }}">Annuler</x-ui.button>
            <x-ui.button type="primary" :submit="true" wire:loading.attr="disabled">
                <span wire:loading.remove>Enregistrer le devis</span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Enregistrement...
                </span>
            </x-ui.button>
        </div>
    </form>
</div>
