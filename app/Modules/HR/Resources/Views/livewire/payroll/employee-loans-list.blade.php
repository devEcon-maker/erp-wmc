<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Prêts employés</h1>
        <p class="text-gray-600 dark:text-gray-400">Gestion des prêts accordés aux employés</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">Demandes en attente</div>
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending_count'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">Prêts actifs</div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['active_count'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">Encours total</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_active'], 0, ',', ' ') }}</div>
            <div class="text-xs text-gray-500">FCFA</div>
        </div>
    </div>

    <!-- Filtres et Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Rechercher un employé..."
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">

            <select wire:model.live="statusFilter"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">Tous les statuts</option>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <button wire:click="openCreateModal"
                class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouveau prêt
        </button>
    </div>

    <!-- Liste des prêts -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Employé</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Échéances</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mensualité</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Restant</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($loans as $loan)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                                            <span class="text-primary-600 dark:text-primary-400 font-medium text-sm">
                                                {{ substr($loan->employee->first_name, 0, 1) }}{{ substr($loan->employee->last_name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $loan->employee->full_name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            Début: {{ $loan->start_date->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <div class="font-semibold text-gray-900 dark:text-white">{{ number_format($loan->amount, 0, ',', ' ') }}</div>
                                @if($loan->interest_rate > 0)
                                    <div class="text-xs text-gray-500">{{ $loan->interest_rate }}% intérêt</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                {{ $loan->installments - $loan->remaining_installments }} / {{ $loan->installments }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                                {{ number_format($loan->monthly_payment, 0, ',', ' ') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-orange-600">
                                {{ number_format($loan->remaining_amount, 0, ',', ' ') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($loan->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($loan->status === 'approved') bg-blue-100 text-blue-800
                                    @elseif($loan->status === 'active') bg-green-100 text-green-800
                                    @elseif($loan->status === 'completed') bg-gray-100 text-gray-800
                                    @elseif($loan->status === 'rejected') bg-red-100 text-red-800
                                    @endif">
                                    {{ $statuses[$loan->status] ?? $loan->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex justify-end gap-2">
                                    <button wire:click="showLoanDetail({{ $loan->id }})"
                                            class="text-primary-600 hover:text-primary-800">
                                        Détails
                                    </button>
                                    @if($loan->status === 'pending')
                                        <button wire:click="approveLoan({{ $loan->id }})"
                                                class="text-green-600 hover:text-green-800">
                                            Approuver
                                        </button>
                                        <button wire:click="rejectLoan({{ $loan->id }})"
                                                class="text-red-600 hover:text-red-800">
                                            Refuser
                                        </button>
                                    @elseif($loan->status === 'approved')
                                        <button wire:click="activateLoan({{ $loan->id }})"
                                                class="text-blue-600 hover:text-blue-800">
                                            Décaisser
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                Aucun prêt trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $loans->links() }}
    </div>

    <!-- Modal Création -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50" wire:click="$set('showCreateModal', false)"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Nouveau prêt employé</h3>
                    </div>

                    <form wire:submit="createLoan" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employé</label>
                            <select wire:model="loanForm.employee_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Sélectionner un employé</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                        {{ $employee->full_name }} ({{ $employee->employee_id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('loanForm.employee_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Montant (FCFA)</label>
                                <input type="number" wire:model.live="loanForm.amount" min="10000" step="10000"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('loanForm.amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Taux d'intérêt (%)</label>
                                <input type="number" wire:model.live="loanForm.interest_rate" min="0" max="50" step="0.5"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('loanForm.interest_rate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre d'échéances</label>
                                <input type="number" wire:model.live="loanForm.installments" min="1" max="60"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('loanForm.installments') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date de début</label>
                                <input type="date" wire:model="loanForm.start_date"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('loanForm.start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        @if($this->monthlyPayment > 0)
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Mensualité estimée</div>
                                <div class="text-xl font-bold text-blue-600">{{ number_format($this->monthlyPayment, 0, ',', ' ') }} FCFA</div>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motif</label>
                            <textarea wire:model="loanForm.reason" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" wire:click="$set('showCreateModal', false)"
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                Annuler
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                                Créer le prêt
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Détails -->
    @if($showDetailModal && $selectedLoan)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50" wire:click="$set('showDetailModal', false)"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Détails du prêt - {{ $selectedLoan->employee->full_name }}
                        </h3>
                    </div>

                    <div class="p-6">
                        <!-- Résumé -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3">
                                <div class="text-xs text-gray-500">Montant initial</div>
                                <div class="font-semibold text-gray-900 dark:text-white">{{ number_format($selectedLoan->amount, 0, ',', ' ') }}</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3">
                                <div class="text-xs text-gray-500">Total à rembourser</div>
                                <div class="font-semibold text-gray-900 dark:text-white">{{ number_format($selectedLoan->total_amount, 0, ',', ' ') }}</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3">
                                <div class="text-xs text-gray-500">Échéances restantes</div>
                                <div class="font-semibold text-gray-900 dark:text-white">{{ $selectedLoan->remaining_installments }} / {{ $selectedLoan->installments }}</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3">
                                <div class="text-xs text-gray-500">Montant restant</div>
                                <div class="font-semibold text-orange-600">{{ number_format($selectedLoan->remaining_amount, 0, ',', ' ') }}</div>
                            </div>
                        </div>

                        <!-- Historique des paiements -->
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Historique des paiements</h4>
                        @if($selectedLoan->payments->count() > 0)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Montant</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Source</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($selectedLoan->payments as $payment)
                                            <tr>
                                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">{{ $payment->payment_date->format('d/m/Y') }}</td>
                                                <td class="px-4 py-2 text-sm text-right text-green-600">{{ number_format($payment->amount, 0, ',', ' ') }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-500">{{ $payment->source ?? 'Paie' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500 text-sm">Aucun paiement enregistré</p>
                        @endif
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                        <button wire:click="$set('showDetailModal', false)"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
