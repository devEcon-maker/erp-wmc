<div>
    <h2 class="text-lg font-medium text-gray-900 mb-4">Suggestions de Réapprovisionnement</h2>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                        <input type="checkbox" class="rounded text-indigo-600">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stock
                        Actuel</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Seuil
                        Alerte</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Déficit
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($suggestions as $product)
                    @php
                        $stock = $product->stockLevels()->sum('quantity');
                        $deficit = $product->min_stock_alert - $stock;
                    @endphp
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" value="{{ $product->id }}" wire:model="selectedProducts"
                                class="rounded text-indigo-600">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $product->name }} <span class="text-xs text-gray-500">({{ $product->reference }})</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600 font-bold">
                            {{ $stock }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                            {{ $product->min_stock_alert }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                            {{ $deficit }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">Aucune suggestion (Stocks OK)</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-end">
            <button wire:click="createPurchaseOrder"
                class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 disabled:opacity-50"
                @if(empty($selectedProducts)) disabled @endif>
                Créer Commandes
            </button>
        </div>
    </div>
</div>