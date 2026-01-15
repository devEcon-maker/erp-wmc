<div>
    <h2 class="text-lg font-medium text-gray-900 mb-4">Historique des Mouvements</h2>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 mb-6 grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <x-ui.select label="Produit" :options="$products->pluck('name', 'id')" wire:model.live="productId"
                placeholder="Tous les produits" />
        </div>
        <div>
            <x-ui.select label="Entrepôt" :options="$warehouses->pluck('name', 'id')" wire:model.live="warehouseId"
                placeholder="Tous les entrepôts" />
        </div>
        <div>
            <x-ui.select label="Type" :options="['in' => 'Entrée', 'out' => 'Sortie', 'transfer' => 'Transfert', 'adjustment' => 'Ajustement']" wire:model.live="type" placeholder="Tous les types" />
        </div>
        <div>
            <x-ui.input type="date" label="Du" wire:model.live="dateFrom" />
        </div>
        <div>
            <x-ui.input type="date" label="Au" wire:model.live="dateTo" />
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entrepôt
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantité
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Réf /
                        Notes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Par</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($movements as $movement)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $movement->date->format('d/m/Y') }}<br>
                            <span class="text-xs text-gray-400">{{ $movement->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $movement->product->name }} <span
                                class="text-gray-400 text-xs">({{ $movement->product->reference }})</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($movement->type === 'in')
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Entrée</span>
                            @elseif($movement->type === 'out')
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Sortie</span>
                            @elseif($movement->type === 'transfer')
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Transfert</span>
                            @else
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($movement->type) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $movement->warehouse->name }}
                            @if($movement->type === 'transfer' && $movement->fromWarehouse)
                                <br><span class="text-xs text-gray-400">De: {{ $movement->fromWarehouse->name }}</span>
                            @endif
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold {{ $movement->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($movement->reference)
                                <span class="font-medium text-indigo-600">{{ class_basename($movement->reference_type) }}
                                    #{{ $movement->reference->reference ?? $movement->reference->id }}</span>
                            @endif
                            @if($movement->notes)
                                <div class="text-xs text-gray-400 mt-1 max-w-xs truncate" title="{{ $movement->notes }}">
                                    {{ $movement->notes }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $movement->creator->name ?? 'System' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">Aucun mouvement trouvé</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-200">
            {{ $movements->links() }}
        </div>
    </div>
</div>