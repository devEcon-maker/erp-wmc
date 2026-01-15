<div>
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Commandes Fournisseurs</h1>
            <p class="text-text-secondary text-sm">Suivi des achats et approvisionnements.</p>
        </div>
        <a href="{{ route('inventory.purchases.create') }}"
            class="bg-primary text-white px-4 py-2 rounded-xl hover:bg-primary-hover transition-colors shadow-lg shadow-primary/20 font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">add_circle</span>
            Nouvelle Commande
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-surface-dark border border-[#3a2e24] p-4 rounded-2xl mb-6 flex flex-wrap gap-4 items-end">
        <div class="w-full md:w-1/4">
            <x-ui.input label="Recherche" placeholder="Référence..." wire:model.live.debounce.300ms="search" />
        </div>
        <div class="w-full md:w-1/4">
            <x-ui.select label="Fournisseur" :options="$suppliers->pluck('company_name', 'id')"
                wire:model.live="supplierId" placeholder="Tous les fournisseurs" />
        </div>
        <div class="w-full md:w-1/4">
            <x-ui.select label="Statut" :options="[
        'draft' => 'Brouillon',
        'sent' => 'Envoyée',
        'partial' => 'Réception Partielle',
        'received' => 'Reçue',
        'cancelled' => 'Annulée'
    ]" wire:model.live="status"
                placeholder="Tous les statuts" />
        </div>
    </div>

    <!-- Table -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#3a2e24]">
                <thead class="bg-surface-highlight">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Référence</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Fournisseur</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Date</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Total TTC</th>
                        <th
                            class="px-6 py-3 text-center text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#3a2e24]">
                    @forelse($orders as $order)
                        <tr class="hover:bg-surface-highlight/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">
                                <a href="{{ route('inventory.purchases.show', $order) }}"
                                    class="hover:underline">{{ $order->reference }}</a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $order->supplier->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">
                                {{ $order->date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-white">
                                {{ number_format($order->total_ttc, 2, ',', ' ') }} FCFA
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <x-ui.badge :status="$order->status" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('inventory.purchases.show', $order) }}"
                                    class="text-text-secondary hover:text-primary transition-colors">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-text-secondary">Aucune commande trouvée</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-[#3a2e24]">
            {{ $orders->links() }}
        </div>
    </div>
</div>