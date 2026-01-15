<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Avances sur salaire</h1>
        <p class="text-gray-600 dark:text-gray-400">Gestion des demandes d'avances sur salaire</p>
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

            <input type="month" wire:model.live="monthFilter"
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>

        <button wire:click="openCreateModal"
                class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouvelle demande
        </button>
    </div>

    <!-- Liste des avances -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Employé</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date demande</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Remboursement</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($advances as $advance)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                                            <span class="text-primary-600 dark:text-primary-400 font-medium text-sm">
                                                {{ substr($advance->employee->first_name, 0, 1) }}{{ substr($advance->employee->last_name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $advance->employee->full_name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $advance->employee->employee_id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $advance->request_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900 dark:text-white">
                                {{ number_format($advance->amount, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $advance->repayment_month->format('F Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($advance->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($advance->status === 'approved') bg-blue-100 text-blue-800
                                    @elseif($advance->status === 'paid') bg-green-100 text-green-800
                                    @elseif($advance->status === 'rejected') bg-red-100 text-red-800
                                    @elseif($advance->is_repaid) bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $statuses[$advance->status] ?? $advance->status }}
                                    @if($advance->is_repaid) (Remboursée) @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex justify-end gap-2">
                                    @if($advance->status === 'pending')
                                        <button wire:click="openApprovalModal({{ $advance->id }})"
                                                class="text-primary-600 hover:text-primary-800">
                                            Traiter
                                        </button>
                                    @elseif($advance->status === 'approved')
                                        <button wire:click="markAsPaid({{ $advance->id }})"
                                                class="text-green-600 hover:text-green-800">
                                            Marquer payée
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                Aucune demande d'avance trouvée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $advances->links() }}
    </div>

    <!-- Modal Création -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50" wire:click="$set('showCreateModal', false)"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Nouvelle demande d'avance</h3>
                    </div>

                    <form wire:submit="createAdvance" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employé</label>
                            <select wire:model="advanceForm.employee_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Sélectionner un employé</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                        {{ $employee->full_name }} ({{ $employee->employee_id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('advanceForm.employee_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Montant (FCFA)</label>
                            <input type="number" wire:model="advanceForm.amount" min="1000" step="1000"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('advanceForm.amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mois de remboursement</label>
                            <input type="month" wire:model="advanceForm.repayment_month"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('advanceForm.repayment_month') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motif</label>
                            <textarea wire:model="advanceForm.reason" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                            @error('advanceForm.reason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" wire:click="$set('showCreateModal', false)"
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                Annuler
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                                Créer la demande
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Approbation -->
    @if($showApprovalModal && $selectedAdvance)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50" wire:click="$set('showApprovalModal', false)"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Traiter la demande d'avance</h3>
                    </div>

                    <div class="p-6">
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 mb-4">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $selectedAdvance->employee->full_name }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">
                                {{ number_format($selectedAdvance->amount, 0, ',', ' ') }} FCFA
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                Remboursement: {{ $selectedAdvance->repayment_month->format('F Y') }}
                            </p>
                            @if($selectedAdvance->reason)
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                    Motif: {{ $selectedAdvance->reason }}
                                </p>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                            <textarea wire:model="approvalNotes" rows="3"
                                      placeholder="Notes optionnelles (requises en cas de refus)"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                            @error('approvalNotes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end gap-3">
                            <button wire:click="rejectAdvance"
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                Refuser
                            </button>
                            <button wire:click="approveAdvance"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                Approuver
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
