<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start gap-4 bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div class="flex items-center gap-4">
            <div class="h-16 w-16 rounded-xl bg-primary/20 flex items-center justify-center">
                <span class="text-2xl font-bold text-primary">
                    {{ strtoupper(substr($contact->display_name, 0, 2)) }}
                </span>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-white">{{ $contact->display_name }}</h2>
                <div class="mt-1 flex items-center gap-3 text-sm">
                    @php
                        $typeColors = [
                            'client' => 'bg-green-500/20 text-green-400 border-green-500/30',
                            'prospect' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
                            'fournisseur' => 'bg-orange-500/20 text-orange-400 border-orange-500/30',
                        ];
                    @endphp
                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium border {{ $typeColors[$contact->type] ?? 'bg-gray-500/20 text-gray-400 border-gray-500/30' }}">
                        {{ ucfirst($contact->type) }}
                    </span>
                    @if($contact->email)
                        <span class="text-text-secondary flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">mail</span>
                            {{ $contact->email }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($contact->type === 'prospect')
                <button wire:click="convertToClient"
                    class="flex items-center gap-2 px-4 py-2 bg-green-500/20 text-green-400 border border-green-500/30 rounded-xl hover:bg-green-500/30 transition-colors">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                    Convertir en Client
                </button>
            @endif
            <a href="{{ route('crm.contacts.edit', $contact) }}"
                class="flex items-center gap-2 px-4 py-2 bg-surface-highlight text-white border border-[#3a2e24] rounded-xl hover:bg-surface-highlight/80 transition-colors">
                <span class="material-symbols-outlined text-[20px]">edit</span>
                Modifier
            </a>
            <button wire:click="delete" wire:confirm="Êtes-vous sûr de vouloir supprimer ce contact ?"
                class="flex items-center gap-2 px-4 py-2 bg-red-500/20 text-red-400 border border-red-500/30 rounded-xl hover:bg-red-500/30 transition-colors">
                <span class="material-symbols-outlined text-[20px]">delete</span>
                Supprimer
            </button>
        </div>
    </div>

    <!-- Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sidebar Info -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Coordonnées -->
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">contact_mail</span>
                    Coordonnées
                </h3>
                <div class="space-y-4">
                    @if($contact->company_name)
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-text-secondary text-[20px] mt-0.5">business</span>
                            <div>
                                <p class="text-xs text-text-secondary">Entreprise</p>
                                <p class="text-white font-medium">{{ $contact->company_name }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-text-secondary text-[20px] mt-0.5">person</span>
                        <div>
                            <p class="text-xs text-text-secondary">Nom Complet</p>
                            <p class="text-white font-medium">{{ $contact->full_name }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-text-secondary text-[20px] mt-0.5">mail</span>
                        <div>
                            <p class="text-xs text-text-secondary">Email</p>
                            <a href="mailto:{{ $contact->email }}" class="text-primary hover:text-primary/80">
                                {{ $contact->email }}
                            </a>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-text-secondary text-[20px] mt-0.5">phone</span>
                        <div>
                            <p class="text-xs text-text-secondary">Téléphone</p>
                            <p class="text-white">{{ $contact->phone ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-text-secondary text-[20px] mt-0.5">smartphone</span>
                        <div>
                            <p class="text-xs text-text-secondary">Mobile</p>
                            <p class="text-white">{{ $contact->mobile ?? '-' }}</p>
                        </div>
                    </div>
                    @if($contact->address || $contact->city)
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-text-secondary text-[20px] mt-0.5">location_on</span>
                            <div>
                                <p class="text-xs text-text-secondary">Adresse</p>
                                <p class="text-white">
                                    @if($contact->address){{ $contact->address }}<br>@endif
                                    @if($contact->postal_code || $contact->city){{ $contact->postal_code }} {{ $contact->city }}<br>@endif
                                    {{ $contact->country }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Infos -->
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">info</span>
                    Informations
                </h3>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-text-secondary text-[20px] mt-0.5">assignment_ind</span>
                        <div>
                            <p class="text-xs text-text-secondary">Assigné à</p>
                            @if($contact->user)
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="h-6 w-6 rounded-full bg-primary/20 flex items-center justify-center text-xs font-bold text-primary">
                                        {{ substr($contact->user->name, 0, 1) }}
                                    </div>
                                    <span class="text-white">{{ $contact->user->name }}</span>
                                </div>
                            @else
                                <p class="text-text-secondary">-</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-text-secondary text-[20px] mt-0.5">source</span>
                        <div>
                            <p class="text-xs text-text-secondary">Source</p>
                            <p class="text-white">{{ $contact->source ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-text-secondary text-[20px] mt-0.5">toggle_on</span>
                        <div>
                            <p class="text-xs text-text-secondary">Statut</p>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium {{ $contact->status === 'active' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                {{ $contact->status === 'active' ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats rapides -->
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">insights</span>
                    Statistiques
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-surface-highlight rounded-lg">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-400 text-[18px]">trending_up</span>
                            <span class="text-sm text-text-secondary">Opportunités</span>
                        </div>
                        <span class="text-white font-bold">{{ $contact->opportunities->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-surface-highlight rounded-lg">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-orange-400 text-[18px]">shopping_cart</span>
                            <span class="text-sm text-text-secondary">Commandes</span>
                        </div>
                        <span class="text-white font-bold">{{ $contact->orders->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-surface-highlight rounded-lg">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-green-400 text-[18px]">receipt_long</span>
                            <span class="text-sm text-text-secondary">Factures</span>
                        </div>
                        <span class="text-white font-bold">{{ $contact->invoices->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-surface-highlight rounded-lg">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-purple-400 text-[18px]">description</span>
                            <span class="text-sm text-text-secondary">Contrats</span>
                        </div>
                        <span class="text-white font-bold">{{ $contact->contracts->count() }}</span>
                    </div>
                    <div class="pt-3 border-t border-[#3a2e24]">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-text-secondary">CA Total</span>
                            <span class="text-lg font-bold text-primary">{{ number_format($contact->invoices->sum('total_amount_ttc'), 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Tabs -->
        <div class="lg:col-span-2">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl overflow-hidden min-h-[500px]">
                <!-- Tabs Navigation -->
                <div class="border-b border-[#3a2e24] bg-surface-highlight/50">
                    <nav class="flex flex-wrap" aria-label="Tabs">
                        @foreach([
                            'infos' => ['icon' => 'notes', 'label' => 'Notes'],
                            'opportunites' => ['icon' => 'trending_up', 'label' => 'Opportunités'],
                            'commandes' => ['icon' => 'shopping_cart', 'label' => 'Commandes'],
                            'factures' => ['icon' => 'receipt_long', 'label' => 'Factures'],
                            'contrats' => ['icon' => 'description', 'label' => 'Contrats'],
                            'history' => ['icon' => 'history', 'label' => 'Historique']
                        ] as $key => $tab)
                            <button wire:click="setActiveTab('{{ $key }}')"
                                class="flex-1 flex items-center justify-center gap-2 py-4 px-3 text-sm font-medium transition-all border-b-2 {{ $activeTab === $key ? 'border-primary text-primary bg-primary/5' : 'border-transparent text-text-secondary hover:text-white hover:bg-surface-highlight' }}">
                                <span class="material-symbols-outlined text-[20px]">{{ $tab['icon'] }}</span>
                                <span class="hidden sm:inline">{{ $tab['label'] }}</span>
                            </button>
                        @endforeach
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    @if($activeTab === 'infos')
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">notes</span>
                                    Notes
                                </h3>
                            </div>
                            <div class="bg-surface-highlight rounded-xl p-4 text-text-secondary whitespace-pre-wrap min-h-[200px]">
                                {{ $contact->notes ?: 'Aucune note.' }}
                            </div>
                        </div>

                    @elseif($activeTab === 'opportunites')
                        @if($contact->opportunities && $contact->opportunities->count() > 0)
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary">trending_up</span>
                                        Opportunités ({{ $contact->opportunities->count() }})
                                    </h3>
                                    <a href="{{ route('crm.opportunities.create', ['contact_id' => $contact->id]) }}"
                                        class="flex items-center gap-1 text-sm text-primary hover:text-primary/80">
                                        <span class="material-symbols-outlined text-[18px]">add</span>
                                        Nouvelle opportunité
                                    </a>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead class="bg-surface-highlight">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Titre</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Étape</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-text-secondary uppercase">Montant</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-text-secondary uppercase">Prob.</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-text-secondary uppercase">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-[#3a2e24]">
                                            @foreach($contact->opportunities as $opportunity)
                                                <tr class="hover:bg-surface-highlight/50">
                                                    <td class="px-4 py-3">
                                                        <a href="{{ route('crm.opportunities.show', $opportunity) }}"
                                                            class="text-primary hover:text-primary/80 font-medium">
                                                            {{ $opportunity->title }}
                                                        </a>
                                                        @if($opportunity->expected_close_date)
                                                            <p class="text-xs text-text-secondary">
                                                                Clôture: {{ $opportunity->expected_close_date->format('d/m/Y') }}
                                                            </p>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        @if($opportunity->stage)
                                                            <span class="px-2 py-1 text-xs rounded-full" style="background-color: {{ $opportunity->stage->color }}20; color: {{ $opportunity->stage->color }}">
                                                                {{ $opportunity->stage->name }}
                                                            </span>
                                                        @else
                                                            <span class="text-text-secondary">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-white text-right font-medium">
                                                        {{ number_format($opportunity->amount, 0, ',', ' ') }} FCFA
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="text-sm text-white">{{ $opportunity->probability }}%</span>
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        @php
                                                            $statusColors = [
                                                                'open' => 'bg-blue-500/20 text-blue-400',
                                                                'won' => 'bg-green-500/20 text-green-400',
                                                                'lost' => 'bg-red-500/20 text-red-400',
                                                            ];
                                                            $statusLabels = [
                                                                'open' => 'En cours',
                                                                'won' => 'Gagnée',
                                                                'lost' => 'Perdue',
                                                            ];
                                                        @endphp
                                                        <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$opportunity->status] ?? 'bg-gray-500/20 text-gray-400' }}">
                                                            {{ $statusLabels[$opportunity->status] ?? ucfirst($opportunity->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Résumé -->
                                <div class="grid grid-cols-3 gap-4 pt-4 border-t border-[#3a2e24]">
                                    <div class="bg-surface-highlight rounded-lg p-3 text-center">
                                        <p class="text-lg font-bold text-white">{{ $contact->opportunities->where('status', 'open')->count() }}</p>
                                        <p class="text-xs text-text-secondary">En cours</p>
                                    </div>
                                    <div class="bg-surface-highlight rounded-lg p-3 text-center">
                                        <p class="text-lg font-bold text-green-400">{{ number_format($contact->opportunities->where('status', 'won')->sum('amount'), 0, ',', ' ') }}</p>
                                        <p class="text-xs text-text-secondary">Gagnées (FCFA)</p>
                                    </div>
                                    <div class="bg-surface-highlight rounded-lg p-3 text-center">
                                        <p class="text-lg font-bold text-primary">{{ number_format($contact->opportunities->where('status', 'open')->sum('weighted_amount'), 0, ',', ' ') }}</p>
                                        <p class="text-xs text-text-secondary">Pipeline pondéré</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-16 text-center">
                                <div class="h-16 w-16 rounded-full bg-blue-500/20 flex items-center justify-center mb-4">
                                    <span class="material-symbols-outlined text-3xl text-blue-400">trending_up</span>
                                </div>
                                <h3 class="text-lg font-medium text-white mb-2">Aucune opportunité</h3>
                                <p class="text-text-secondary mb-4">Ce contact n'a pas encore d'opportunités.</p>
                                <a href="{{ route('crm.opportunities.create', ['contact_id' => $contact->id]) }}"
                                    class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl hover:bg-primary/80 transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">add</span>
                                    Créer une opportunité
                                </a>
                            </div>
                        @endif

                    @elseif($activeTab === 'commandes')
                        @if($contact->orders && $contact->orders->count() > 0)
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary">shopping_cart</span>
                                        Commandes ({{ $contact->orders->count() }})
                                    </h3>
                                    <a href="{{ route('crm.orders.create', ['contact_id' => $contact->id]) }}"
                                        class="flex items-center gap-1 text-sm text-primary hover:text-primary/80">
                                        <span class="material-symbols-outlined text-[18px]">add</span>
                                        Nouvelle commande
                                    </a>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead class="bg-surface-highlight">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Référence</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Date</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Livraison</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-text-secondary uppercase">Montant TTC</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-text-secondary uppercase">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-[#3a2e24]">
                                            @foreach($contact->orders as $order)
                                                <tr class="hover:bg-surface-highlight/50">
                                                    <td class="px-4 py-3">
                                                        <a href="{{ route('crm.orders.show', $order) }}"
                                                            class="text-primary hover:text-primary/80 font-medium">
                                                            {{ $order->reference }}
                                                        </a>
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-text-secondary">
                                                        {{ $order->order_date?->format('d/m/Y') }}
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-text-secondary">
                                                        @if($order->shipped_at)
                                                            <span class="text-green-400">{{ $order->shipped_at->format('d/m/Y') }}</span>
                                                        @elseif($order->delivery_date)
                                                            {{ $order->delivery_date->format('d/m/Y') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-white text-right font-medium">
                                                        {{ number_format($order->total_amount_ttc, 0, ',', ' ') }} FCFA
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        @php
                                                            $orderStatusColors = [
                                                                'draft' => 'bg-gray-500/20 text-gray-400',
                                                                'confirmed' => 'bg-blue-500/20 text-blue-400',
                                                                'processing' => 'bg-orange-500/20 text-orange-400',
                                                                'shipped' => 'bg-purple-500/20 text-purple-400',
                                                                'delivered' => 'bg-green-500/20 text-green-400',
                                                                'cancelled' => 'bg-red-500/20 text-red-400',
                                                            ];
                                                            $orderStatusLabels = [
                                                                'draft' => 'Brouillon',
                                                                'confirmed' => 'Confirmée',
                                                                'processing' => 'En cours',
                                                                'shipped' => 'Expédiée',
                                                                'delivered' => 'Livrée',
                                                                'cancelled' => 'Annulée',
                                                            ];
                                                        @endphp
                                                        <span class="px-2 py-1 text-xs rounded-full {{ $orderStatusColors[$order->status] ?? 'bg-gray-500/20 text-gray-400' }}">
                                                            {{ $orderStatusLabels[$order->status] ?? ucfirst($order->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Résumé -->
                                <div class="grid grid-cols-3 gap-4 pt-4 border-t border-[#3a2e24]">
                                    <div class="bg-surface-highlight rounded-lg p-3 text-center">
                                        <p class="text-lg font-bold text-white">{{ $contact->orders->count() }}</p>
                                        <p class="text-xs text-text-secondary">Total commandes</p>
                                    </div>
                                    <div class="bg-surface-highlight rounded-lg p-3 text-center">
                                        <p class="text-lg font-bold text-green-400">{{ $contact->orders->where('status', 'delivered')->count() }}</p>
                                        <p class="text-xs text-text-secondary">Livrées</p>
                                    </div>
                                    <div class="bg-surface-highlight rounded-lg p-3 text-center">
                                        <p class="text-lg font-bold text-primary">{{ number_format($contact->orders->sum('total_amount_ttc'), 0, ',', ' ') }}</p>
                                        <p class="text-xs text-text-secondary">Total (FCFA)</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-16 text-center">
                                <div class="h-16 w-16 rounded-full bg-orange-500/20 flex items-center justify-center mb-4">
                                    <span class="material-symbols-outlined text-3xl text-orange-400">shopping_cart</span>
                                </div>
                                <h3 class="text-lg font-medium text-white mb-2">Aucune commande</h3>
                                <p class="text-text-secondary mb-4">Ce contact n'a pas encore de commandes.</p>
                                <a href="{{ route('crm.orders.create', ['contact_id' => $contact->id]) }}"
                                    class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl hover:bg-primary/80 transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">add</span>
                                    Créer une commande
                                </a>
                            </div>
                        @endif

                    @elseif($activeTab === 'factures')
                        @if($contact->invoices && $contact->invoices->count() > 0)
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary">receipt_long</span>
                                        Factures ({{ $contact->invoices->count() }})
                                    </h3>
                                    <a href="{{ route('finance.invoices.create', ['contact_id' => $contact->id]) }}"
                                        class="flex items-center gap-1 text-sm text-primary hover:text-primary/80">
                                        <span class="material-symbols-outlined text-[18px]">add</span>
                                        Nouvelle facture
                                    </a>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead class="bg-surface-highlight">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">N°</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Date</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-text-secondary uppercase">Montant</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-text-secondary uppercase">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-[#3a2e24]">
                                            @foreach($contact->invoices as $invoice)
                                                <tr class="hover:bg-surface-highlight/50">
                                                    <td class="px-4 py-3">
                                                        <a href="{{ route('finance.invoices.show', $invoice) }}"
                                                            class="text-primary hover:text-primary/80 font-medium">
                                                            {{ $invoice->invoice_number }}
                                                        </a>
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-text-secondary">
                                                        {{ $invoice->issue_date?->format('d/m/Y') }}
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-white text-right font-medium">
                                                        {{ number_format($invoice->total_amount_ttc, 0, ',', ' ') }} FCFA
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        @php
                                                            $statusColors = [
                                                                'draft' => 'bg-gray-500/20 text-gray-400',
                                                                'sent' => 'bg-blue-500/20 text-blue-400',
                                                                'paid' => 'bg-green-500/20 text-green-400',
                                                                'overdue' => 'bg-red-500/20 text-red-400',
                                                                'cancelled' => 'bg-red-500/20 text-red-400',
                                                            ];
                                                        @endphp
                                                        <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$invoice->status] ?? 'bg-gray-500/20 text-gray-400' }}">
                                                            {{ ucfirst($invoice->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-16 text-center">
                                <div class="h-16 w-16 rounded-full bg-green-500/20 flex items-center justify-center mb-4">
                                    <span class="material-symbols-outlined text-3xl text-green-400">receipt_long</span>
                                </div>
                                <h3 class="text-lg font-medium text-white mb-2">Aucune facture</h3>
                                <p class="text-text-secondary mb-4">Ce contact n'a pas encore de factures.</p>
                                <a href="{{ route('finance.invoices.create', ['contact_id' => $contact->id]) }}"
                                    class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl hover:bg-primary/80 transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">add</span>
                                    Créer une facture
                                </a>
                            </div>
                        @endif

                    @elseif($activeTab === 'contrats')
                        @if($contact->contracts && $contact->contracts->count() > 0)
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary">description</span>
                                        Contrats ({{ $contact->contracts->count() }})
                                    </h3>
                                    <a href="{{ route('crm.contracts.create', ['contact_id' => $contact->id]) }}"
                                        class="flex items-center gap-1 text-sm text-primary hover:text-primary/80">
                                        <span class="material-symbols-outlined text-[18px]">add</span>
                                        Nouveau contrat
                                    </a>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead class="bg-surface-highlight">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Référence</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Type</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Période</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-text-secondary uppercase">Montant TTC</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-text-secondary uppercase">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-[#3a2e24]">
                                            @foreach($contact->contracts as $contract)
                                                <tr class="hover:bg-surface-highlight/50">
                                                    <td class="px-4 py-3">
                                                        <a href="{{ route('crm.contracts.show', $contract) }}"
                                                            class="text-primary hover:text-primary/80 font-medium">
                                                            {{ $contract->reference }}
                                                        </a>
                                                        @if($contract->has_document)
                                                            <span class="material-symbols-outlined text-[14px] text-text-secondary ml-1" title="Document attaché">attach_file</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-text-secondary">
                                                        {{ $contract->subtype_label }}
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-text-secondary">
                                                        @if($contract->start_date && $contract->end_date)
                                                            {{ $contract->start_date->format('d/m/Y') }} - {{ $contract->end_date->format('d/m/Y') }}
                                                        @elseif($contract->start_date)
                                                            Depuis {{ $contract->start_date->format('d/m/Y') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-white text-right font-medium">
                                                        {{ number_format($contract->total_amount_ttc, 0, ',', ' ') }} FCFA
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        @php
                                                            $contractStatusColors = [
                                                                'draft' => 'bg-gray-500/20 text-gray-400',
                                                                'active' => 'bg-green-500/20 text-green-400',
                                                                'suspended' => 'bg-orange-500/20 text-orange-400',
                                                                'expired' => 'bg-red-500/20 text-red-400',
                                                                'terminated' => 'bg-red-500/20 text-red-400',
                                                            ];
                                                        @endphp
                                                        <span class="px-2 py-1 text-xs rounded-full {{ $contractStatusColors[$contract->status] ?? 'bg-gray-500/20 text-gray-400' }}">
                                                            {{ $contract->status_label }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Résumé -->
                                <div class="grid grid-cols-3 gap-4 pt-4 border-t border-[#3a2e24]">
                                    <div class="bg-surface-highlight rounded-lg p-3 text-center">
                                        <p class="text-lg font-bold text-white">{{ $contact->contracts->count() }}</p>
                                        <p class="text-xs text-text-secondary">Total contrats</p>
                                    </div>
                                    <div class="bg-surface-highlight rounded-lg p-3 text-center">
                                        <p class="text-lg font-bold text-green-400">{{ $contact->contracts->where('status', 'active')->count() }}</p>
                                        <p class="text-xs text-text-secondary">Actifs</p>
                                    </div>
                                    <div class="bg-surface-highlight rounded-lg p-3 text-center">
                                        <p class="text-lg font-bold text-primary">{{ number_format($contact->contracts->where('status', 'active')->sum('total_amount_ttc'), 0, ',', ' ') }}</p>
                                        <p class="text-xs text-text-secondary">Valeur active (FCFA)</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-16 text-center">
                                <div class="h-16 w-16 rounded-full bg-purple-500/20 flex items-center justify-center mb-4">
                                    <span class="material-symbols-outlined text-3xl text-purple-400">description</span>
                                </div>
                                <h3 class="text-lg font-medium text-white mb-2">Aucun contrat</h3>
                                <p class="text-text-secondary mb-4">Ce contact n'a pas encore de contrats.</p>
                                <a href="{{ route('crm.contracts.create', ['contact_id' => $contact->id]) }}"
                                    class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl hover:bg-primary/80 transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">add</span>
                                    Créer un contrat
                                </a>
                            </div>
                        @endif

                    @elseif($activeTab === 'history')
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">history</span>
                                    Historique des activités
                                </h3>
                            </div>

                            <div class="space-y-3">
                                <!-- Création du contact -->
                                <div class="flex gap-4 p-4 bg-surface-highlight rounded-xl">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-green-500/20 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-green-400">person_add</span>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-white font-medium">Contact créé</p>
                                        <p class="text-sm text-text-secondary">{{ $contact->created_at->format('d/m/Y à H:i') }}</p>
                                    </div>
                                </div>

                                @if($contact->converted_at)
                                    <div class="flex gap-4 p-4 bg-surface-highlight rounded-xl">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-primary/20 flex items-center justify-center">
                                                <span class="material-symbols-outlined text-primary">check_circle</span>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-white font-medium">Converti en client</p>
                                            <p class="text-sm text-text-secondary">{{ $contact->converted_at->format('d/m/Y à H:i') }}</p>
                                        </div>
                                    </div>
                                @endif

                                <!-- Opportunités -->
                                @foreach($contact->opportunities->sortByDesc('created_at')->take(5) as $opp)
                                    <div class="flex gap-4 p-4 bg-surface-highlight rounded-xl">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-blue-500/20 flex items-center justify-center">
                                                <span class="material-symbols-outlined text-blue-400">trending_up</span>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-white font-medium">
                                                Opportunité créée:
                                                <a href="{{ route('crm.opportunities.show', $opp) }}" class="text-primary hover:text-primary/80">{{ $opp->title }}</a>
                                            </p>
                                            <p class="text-sm text-text-secondary">
                                                {{ number_format($opp->amount, 0, ',', ' ') }} FCFA - {{ $opp->created_at->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Commandes -->
                                @foreach($contact->orders->sortByDesc('created_at')->take(5) as $ord)
                                    <div class="flex gap-4 p-4 bg-surface-highlight rounded-xl">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-orange-500/20 flex items-center justify-center">
                                                <span class="material-symbols-outlined text-orange-400">shopping_cart</span>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-white font-medium">
                                                Commande créée:
                                                <a href="{{ route('crm.orders.show', $ord) }}" class="text-primary hover:text-primary/80">{{ $ord->reference }}</a>
                                            </p>
                                            <p class="text-sm text-text-secondary">
                                                {{ number_format($ord->total_amount_ttc, 0, ',', ' ') }} FCFA - {{ $ord->created_at->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Factures -->
                                @foreach($contact->invoices->sortByDesc('created_at')->take(5) as $inv)
                                    <div class="flex gap-4 p-4 bg-surface-highlight rounded-xl">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-green-500/20 flex items-center justify-center">
                                                <span class="material-symbols-outlined text-green-400">receipt_long</span>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-white font-medium">
                                                Facture créée:
                                                <a href="{{ route('finance.invoices.show', $inv) }}" class="text-primary hover:text-primary/80">{{ $inv->invoice_number }}</a>
                                            </p>
                                            <p class="text-sm text-text-secondary">
                                                {{ number_format($inv->total_amount_ttc, 0, ',', ' ') }} FCFA - {{ $inv->created_at->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Contrats -->
                                @foreach($contact->contracts->sortByDesc('created_at')->take(5) as $ctr)
                                    <div class="flex gap-4 p-4 bg-surface-highlight rounded-xl">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-purple-500/20 flex items-center justify-center">
                                                <span class="material-symbols-outlined text-purple-400">description</span>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-white font-medium">
                                                Contrat créé:
                                                <a href="{{ route('crm.contracts.show', $ctr) }}" class="text-primary hover:text-primary/80">{{ $ctr->reference }}</a>
                                            </p>
                                            <p class="text-sm text-text-secondary">
                                                {{ $ctr->subtype_label }} - {{ $ctr->created_at->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach

                                @if($contact->opportunities->isEmpty() && $contact->orders->isEmpty() && $contact->invoices->isEmpty() && $contact->contracts->isEmpty() && !$contact->converted_at)
                                    <div class="text-center py-8 text-text-secondary">
                                        <span class="material-symbols-outlined text-4xl mb-2">schedule</span>
                                        <p>Aucune autre activité enregistrée.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
