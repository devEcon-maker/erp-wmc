<div>
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                {{ $purchaseOrder->reference }}
                <x-ui.badge :status="$purchaseOrder->status" />
            </h1>
            <p class="text-gray-500 mt-1">Fournisseur : <a href="#"
                    class="text-indigo-600 hover:underline">{{ $purchaseOrder->supplier->name }}</a></p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('inventory.purchases.index') }}"
                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                Retour
            </a>

            @if($purchaseOrder->status === 'draft')
                <a href="{{ route('inventory.purchases.edit', $purchaseOrder) }}"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Modifier
                </a>
                <button wire:click="send" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    Envoyer
                </button>
            @endif

            @if(in_array($purchaseOrder->status, ['sent', 'partial']))
                <button wire:click="receiveAll" wire:confirm="Confirmer la réception totale ?"
                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                    Réception Totale
                </button>
            @endif

            @if(in_array($purchaseOrder->status, ['draft', 'sent']))
                <button wire:click="cancel" wire:confirm="Êtes-vous sûr de vouloir annuler ?"
                    class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                    Annuler
                </button>
            @endif
        </div>
    </div>

    <!-- Details -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <dt class="text-sm font-medium text-gray-500">Date Commande</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $purchaseOrder->date->format('d/m/Y') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Date Prévue</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $purchaseOrder->expected_date?->format('d/m/Y') ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Total HT</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ number_format($purchaseOrder->total_ht, 2) }} FCFA</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Total TTC</dt>
                <dd class="mt-1 text-lg font-bold text-indigo-600">{{ number_format($purchaseOrder->total_ttc, 2) }} FCFA
                </dd>
            </div>
        </div>
        @if($purchaseOrder->notes)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $purchaseOrder->notes }}</dd>
            </div>
        @endif
    </div>

    <!-- Lines -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Articles</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qté
                        Commande</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Reçu
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Prix U.
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total HT
                    </th>
                    @if(in_array($purchaseOrder->status, ['sent', 'partial']))
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-32 bg-gray-100">
                            Réception</th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($purchaseOrder->lines as $line)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $line->product->name }}<br>
                            <span class="text-xs text-gray-500">{{ $line->description }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">{{ $line->quantity }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ $line->received_qty }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">{{ number_format($line->unit_price, 2) }} FCFA</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            {{ number_format($line->quantity * $line->unit_price, 2) }} FCFA</td>

                        @if(in_array($purchaseOrder->status, ['sent', 'partial']))
                            <td class="px-6 py-4 whitespace-nowrap text-right bg-gray-50">
                                @if($line->quantity > $line->received_qty)
                                    <input type="number" step="0.001" min="0" max="{{ $line->quantity - $line->received_qty }}"
                                        wire:model="receivedQuantities.{{ $line->id }}"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs text-right">
                                @else
                                    <span class="text-green-600 text-xs font-medium">Clôturé</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if(in_array($purchaseOrder->status, ['sent', 'partial']))
            <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                <button wire:click="receivePartial"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm">
                    Valider la réception partielle
                </button>
            </div>
        @endif
    </div>
</div>