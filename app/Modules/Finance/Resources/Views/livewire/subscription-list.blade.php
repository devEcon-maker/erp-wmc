<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Abonnements</h2>
            <p class="text-text-secondary text-sm">Gestion des factures récurrentes (serveurs, licences, services...)</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button wire:click="processAllDue" wire:loading.attr="disabled"
                class="bg-surface-highlight hover:bg-surface-highlight/80 text-white font-medium py-2 px-4 rounded-xl flex items-center gap-2 transition-colors border border-[#3a2e24]">
                <span class="material-symbols-outlined text-[20px]">schedule_send</span>
                Générer factures échues
            </button>
            <button wire:click="create"
                class="bg-primary hover:bg-primary/90 text-white font-bold py-2 px-4 rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Nouvel Abonnement
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-surface-dark border border-[#3a2e24] p-4 rounded-xl">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-green-400">check_circle</span>
                </div>
                <div>
                    <div class="text-sm text-text-secondary">Actifs</div>
                    <div class="text-2xl font-bold text-white">{{ $stats['active_count'] }}</div>
                </div>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] p-4 rounded-xl">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">payments</span>
                </div>
                <div>
                    <div class="text-sm text-text-secondary">Revenus mensuels</div>
                    <div class="text-2xl font-bold text-white">{{ number_format($stats['monthly_revenue'], 0, ',', ' ') }} <span class="text-sm font-normal text-text-secondary">FCFA</span></div>
                </div>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] p-4 rounded-xl">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-orange-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-orange-400">schedule</span>
                </div>
                <div>
                    <div class="text-sm text-text-secondary">Prochains 7 jours</div>
                    <div class="text-2xl font-bold text-orange-400">{{ $stats['due_soon_count'] }}</div>
                </div>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] p-4 rounded-xl">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-400">repeat</span>
                </div>
                <div>
                    <div class="text-sm text-text-secondary">Total abonnements</div>
                    <div class="text-2xl font-bold text-white">{{ $stats['total_subscriptions'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-text-secondary mb-1">Recherche</label>
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="w-full bg-background-dark border border-[#3a2e24] rounded-lg py-2 px-3 text-white placeholder-text-secondary focus:outline-none focus:border-primary"
                    placeholder="Nom, référence, client...">
            </div>
            <div>
                <label class="block text-sm font-medium text-text-secondary mb-1">Statut</label>
                <select wire:model.live="status"
                    class="w-full bg-background-dark border border-[#3a2e24] rounded-lg py-2 px-3 text-white focus:outline-none focus:border-primary">
                    <option value="">Tous les statuts</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-text-secondary mb-1">Fréquence</label>
                <select wire:model.live="frequency"
                    class="w-full bg-background-dark border border-[#3a2e24] rounded-lg py-2 px-3 text-white focus:outline-none focus:border-primary">
                    <option value="">Toutes les fréquences</option>
                    @foreach($frequencies as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-background-dark border-b border-[#3a2e24]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">Abonnement</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">Client</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">Fréquence</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-text-secondary uppercase tracking-wider">Montant TTC</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">Prochaine échéance</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-text-secondary uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-text-secondary uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#3a2e24]">
                    @forelse($subscriptions as $subscription)
                        <tr class="hover:bg-surface-highlight/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-primary/20 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-primary">dns</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-white">{{ $subscription->name }}</div>
                                        <div class="text-xs text-text-secondary">{{ $subscription->reference }}</div>
                                        @if($subscription->lines->count() > 0)
                                            <div class="text-xs text-primary">{{ $subscription->lines->count() }} produit(s)</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-text-secondary">{{ $subscription->contact->display_name }}</div>
                                @if($subscription->contact->company_name)
                                    <div class="text-xs text-text-secondary opacity-70">{{ $subscription->contact->company_name }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[16px] text-text-secondary">repeat</span>
                                    <span class="text-sm text-white">{{ $subscription->frequency_label }}</span>
                                </div>
                                @if($subscription->frequency_interval > 1)
                                    <div class="text-xs text-text-secondary">Tous les {{ $subscription->frequency_interval }} cycles</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="text-sm font-bold text-white">{{ number_format($subscription->amount_ttc, 0, ',', ' ') }} FCFA</div>
                                <div class="text-xs text-text-secondary">HT: {{ number_format($subscription->amount_ht, 0, ',', ' ') }} FCFA</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($subscription->status === 'active')
                                    <div class="text-sm {{ $subscription->is_due_soon ? 'text-orange-400 font-bold' : 'text-white' }}">
                                        {{ $subscription->next_billing_date->format('d/m/Y') }}
                                    </div>
                                    @if($subscription->is_due_soon)
                                        <div class="text-xs text-orange-400">
                                            {{ $subscription->next_billing_date->diffForHumans() }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-sm text-text-secondary">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusColors = [
                                        'active' => 'bg-green-500/20 text-green-400 border-green-500/30',
                                        'paused' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
                                        'cancelled' => 'bg-red-500/20 text-red-400 border-red-500/30',
                                        'expired' => 'bg-gray-500/20 text-gray-400 border-gray-500/30',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusColors[$subscription->status] ?? '' }}">
                                    {{ $subscription->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-1">
                                    @if($subscription->status === 'active')
                                        <button wire:click="generateInvoice({{ $subscription->id }})"
                                            wire:confirm="Générer une facture pour cet abonnement ?"
                                            class="p-2 rounded-lg hover:bg-primary/20 text-text-secondary hover:text-primary transition-colors"
                                            title="Générer facture">
                                            <span class="material-symbols-outlined text-[20px]">receipt</span>
                                        </button>
                                    @endif
                                    <button wire:click="edit({{ $subscription->id }})"
                                        class="p-2 rounded-lg hover:bg-surface-highlight text-text-secondary hover:text-white transition-colors"
                                        title="Modifier">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </button>
                                    @if($subscription->status === 'active')
                                        <button wire:click="toggleStatus({{ $subscription->id }})"
                                            class="p-2 rounded-lg hover:bg-yellow-500/20 text-text-secondary hover:text-yellow-400 transition-colors"
                                            title="Mettre en pause">
                                            <span class="material-symbols-outlined text-[20px]">pause_circle</span>
                                        </button>
                                    @elseif($subscription->status === 'paused')
                                        <button wire:click="toggleStatus({{ $subscription->id }})"
                                            class="p-2 rounded-lg hover:bg-green-500/20 text-text-secondary hover:text-green-400 transition-colors"
                                            title="Réactiver">
                                            <span class="material-symbols-outlined text-[20px]">play_circle</span>
                                        </button>
                                    @endif
                                    @if($subscription->status !== 'cancelled')
                                        <button wire:click="cancelSubscription({{ $subscription->id }})"
                                            wire:confirm="Êtes-vous sûr de vouloir annuler cet abonnement ?"
                                            class="p-2 rounded-lg hover:bg-red-500/20 text-text-secondary hover:text-red-400 transition-colors"
                                            title="Annuler">
                                            <span class="material-symbols-outlined text-[20px]">cancel</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-text-secondary">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">repeat</span>
                                    <p>Aucun abonnement trouvé.</p>
                                    <button wire:click="create" class="mt-4 text-primary hover:text-primary/80 text-sm font-medium">
                                        + Créer un abonnement
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subscriptions->hasPages())
            <div class="border-t border-[#3a2e24] p-4">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Create/Edit -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm overflow-y-auto">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl w-full max-w-4xl shadow-2xl my-8" @click.away="$wire.closeModal()">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-[#3a2e24]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary">{{ $editingSubscription ? 'edit' : 'add_circle' }}</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">{{ $editingSubscription ? 'Modifier l\'abonnement' : 'Nouvel abonnement' }}</h3>
                            <p class="text-sm text-text-secondary">{{ $editingSubscription ? 'Modifiez les informations de l\'abonnement' : 'Créez un nouvel abonnement récurrent' }}</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="p-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <form wire:submit="save">
                    <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                        <!-- Informations générales -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Client -->
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Client <span class="text-red-400">*</span></label>
                                <select wire:model="contact_id"
                                    class="w-full bg-background-dark border border-[#3a2e24] rounded-xl py-2.5 px-4 text-white focus:outline-none focus:border-primary">
                                    <option value="">Sélectionner un client</option>
                                    @foreach($contacts as $contact)
                                        <option value="{{ $contact->id }}">
                                            {{ $contact->display_name }}
                                            @if($contact->company_name) - {{ $contact->company_name }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('contact_id') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Nom de l'abonnement -->
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Nom de l'abonnement <span class="text-red-400">*</span></label>
                                <input type="text" wire:model="name"
                                    class="w-full bg-background-dark border border-[#3a2e24] rounded-xl py-2.5 px-4 text-white placeholder-text-secondary focus:outline-none focus:border-primary"
                                    placeholder="Ex: Hébergement serveur dédié">
                                @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-white mb-2">Description</label>
                            <textarea wire:model="description" rows="2"
                                class="w-full bg-background-dark border border-[#3a2e24] rounded-xl py-2.5 px-4 text-white placeholder-text-secondary focus:outline-none focus:border-primary resize-none"
                                placeholder="Description détaillée de l'abonnement..."></textarea>
                        </div>

                        <!-- Fréquence et Dates -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Fréquence <span class="text-red-400">*</span></label>
                                <select wire:model="frequency_form"
                                    class="w-full bg-background-dark border border-[#3a2e24] rounded-xl py-2.5 px-4 text-white focus:outline-none focus:border-primary">
                                    @foreach($frequencies as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Intervalle</label>
                                <input type="number" wire:model="frequency_interval" min="1" max="12"
                                    class="w-full bg-background-dark border border-[#3a2e24] rounded-xl py-2.5 px-4 text-white focus:outline-none focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Date de début <span class="text-red-400">*</span></label>
                                <input type="date" wire:model="start_date"
                                    class="w-full bg-background-dark border border-[#3a2e24] rounded-xl py-2.5 px-4 text-white focus:outline-none focus:border-primary">
                                @error('start_date') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Date de fin</label>
                                <input type="date" wire:model="end_date"
                                    class="w-full bg-background-dark border border-[#3a2e24] rounded-xl py-2.5 px-4 text-white focus:outline-none focus:border-primary">
                            </div>
                        </div>

                        <!-- Section Produits/Services -->
                        <div class="border border-[#3a2e24] rounded-xl overflow-hidden">
                            <div class="bg-background-dark px-4 py-3 border-b border-[#3a2e24]">
                                <h4 class="text-white font-medium flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">inventory_2</span>
                                    Produits / Services récurrents
                                </h4>
                            </div>

                            <!-- Ajouter un produit -->
                            <div class="p-4 bg-surface-highlight/30 border-b border-[#3a2e24]">
                                <div class="flex flex-wrap gap-3 items-end">
                                    <div class="flex-1 min-w-[200px]">
                                        <label class="block text-sm text-text-secondary mb-1">Sélectionner un produit</label>
                                        <select wire:model="selectedProduct"
                                            class="w-full bg-background-dark border border-[#3a2e24] rounded-lg py-2 px-3 text-white focus:outline-none focus:border-primary text-sm">
                                            <option value="">-- Choisir un produit --</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">
                                                    {{ $product->name }} - {{ number_format($product->price, 0, ',', ' ') }} FCFA
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="button" wire:click="addProduct"
                                        class="px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg text-sm font-medium flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[18px]">add</span>
                                        Ajouter
                                    </button>
                                    <button type="button" wire:click="addCustomLine"
                                        class="px-4 py-2 bg-surface-highlight hover:bg-surface-highlight/80 text-white rounded-lg text-sm font-medium flex items-center gap-1 border border-[#3a2e24]">
                                        <span class="material-symbols-outlined text-[18px]">edit_note</span>
                                        Ligne personnalisée
                                    </button>
                                </div>
                            </div>

                            <!-- Liste des lignes -->
                            <div class="divide-y divide-[#3a2e24]">
                                @forelse($lines as $index => $line)
                                    <div class="p-4 hover:bg-surface-highlight/20 transition-colors">
                                        <div class="grid grid-cols-12 gap-3 items-start">
                                            <!-- Description -->
                                            <div class="col-span-12 md:col-span-4">
                                                <label class="block text-xs text-text-secondary mb-1">Description</label>
                                                <input type="text" wire:model="lines.{{ $index }}.description"
                                                    class="w-full bg-background-dark border border-[#3a2e24] rounded-lg py-2 px-3 text-white text-sm focus:outline-none focus:border-primary"
                                                    placeholder="Description du produit/service">
                                                @error("lines.{$index}.description") <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>
                                            <!-- Quantité -->
                                            <div class="col-span-4 md:col-span-2">
                                                <label class="block text-xs text-text-secondary mb-1">Qté</label>
                                                <input type="number" wire:model.live="lines.{{ $index }}.quantity" min="1"
                                                    class="w-full bg-background-dark border border-[#3a2e24] rounded-lg py-2 px-3 text-white text-sm focus:outline-none focus:border-primary">
                                            </div>
                                            <!-- Prix unitaire -->
                                            <div class="col-span-4 md:col-span-2">
                                                <label class="block text-xs text-text-secondary mb-1">Prix HT</label>
                                                <input type="number" wire:model.live="lines.{{ $index }}.unit_price" min="0" step="100"
                                                    class="w-full bg-background-dark border border-[#3a2e24] rounded-lg py-2 px-3 text-white text-sm focus:outline-none focus:border-primary">
                                            </div>
                                            <!-- TVA -->
                                            <div class="col-span-4 md:col-span-2">
                                                <label class="block text-xs text-text-secondary mb-1">TVA %</label>
                                                <input type="number" wire:model.live="lines.{{ $index }}.tax_rate" min="0" max="100" step="0.01"
                                                    class="w-full bg-background-dark border border-[#3a2e24] rounded-lg py-2 px-3 text-white text-sm focus:outline-none focus:border-primary">
                                            </div>
                                            <!-- Sous-total & Supprimer -->
                                            <div class="col-span-12 md:col-span-2 flex items-end justify-between md:justify-end gap-2">
                                                @php
                                                    $subtotal = ($line['quantity'] ?? 0) * ($line['unit_price'] ?? 0);
                                                    $lineTtc = $subtotal * (1 + ($line['tax_rate'] ?? 19.25) / 100);
                                                @endphp
                                                <div class="text-right">
                                                    <p class="text-xs text-text-secondary">Total TTC</p>
                                                    <p class="text-sm font-bold text-white">{{ number_format($lineTtc, 0, ',', ' ') }} FCFA</p>
                                                </div>
                                                <button type="button" wire:click="removeLine({{ $index }})"
                                                    class="p-2 text-red-400 hover:bg-red-500/20 rounded-lg transition-colors"
                                                    title="Supprimer">
                                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-8 text-center text-text-secondary">
                                        <span class="material-symbols-outlined text-3xl mb-2 opacity-50">inventory_2</span>
                                        <p class="text-sm">Aucun produit ou service ajouté</p>
                                        <p class="text-xs mt-1">Utilisez les boutons ci-dessus pour ajouter des lignes</p>
                                    </div>
                                @endforelse
                            </div>

                            <!-- Totaux -->
                            @if(count($lines) > 0)
                                <div class="bg-background-dark p-4 border-t border-[#3a2e24]">
                                    <div class="flex justify-end gap-8">
                                        <div class="text-right">
                                            <p class="text-sm text-text-secondary">Total HT</p>
                                            <p class="text-lg font-bold text-white">{{ number_format($totalHt, 0, ',', ' ') }} FCFA</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm text-text-secondary">Total TTC</p>
                                            <p class="text-xl font-bold text-primary">{{ number_format($totalTtc, 0, ',', ' ') }} FCFA</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        @error('lines') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror

                        <!-- Auto-génération -->
                        <div class="flex items-center gap-3 p-4 bg-background-dark rounded-xl border border-[#3a2e24]">
                            <input type="checkbox" wire:model="auto_generate_invoice" id="auto_generate"
                                class="w-5 h-5 rounded border-[#3a2e24] bg-background-dark text-primary focus:ring-primary focus:ring-offset-0">
                            <label for="auto_generate" class="flex-1">
                                <span class="text-white font-medium">Génération automatique des factures</span>
                                <p class="text-sm text-text-secondary">Les factures seront générées automatiquement à chaque échéance</p>
                            </label>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-white mb-2">Notes internes</label>
                            <textarea wire:model="notes" rows="2"
                                class="w-full bg-background-dark border border-[#3a2e24] rounded-xl py-2.5 px-4 text-white placeholder-text-secondary focus:outline-none focus:border-primary resize-none"
                                placeholder="Notes internes sur cet abonnement..."></textarea>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-between gap-3 p-6 border-t border-[#3a2e24]">
                        <div class="text-sm text-text-secondary">
                            @if(count($lines) > 0)
                                <span class="font-medium text-white">{{ count($lines) }}</span> ligne(s) -
                                Total: <span class="font-bold text-primary">{{ number_format($totalTtc, 0, ',', ' ') }} FCFA</span>
                            @endif
                        </div>
                        <div class="flex gap-3">
                            <button type="button" wire:click="closeModal"
                                class="px-4 py-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-xl transition-colors">
                                Annuler
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-primary hover:bg-primary/90 text-white font-bold rounded-xl transition-colors flex items-center gap-2">
                                <span class="material-symbols-outlined text-[20px]">save</span>
                                {{ $editingSubscription ? 'Enregistrer' : 'Créer l\'abonnement' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
