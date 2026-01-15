<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $expenseReport->reference }}</h1>
            <p class="text-gray-500">Note de frais de {{ $expenseReport->employee->full_name }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="px-3 py-1 rounded-full bg-{{ $expenseReport->status_color }}-100 text-{{ $expenseReport->status_color }}-800 font-medium">
                {{ $expenseReport->status_label }}
            </span>
            @if($expenseReport->status === 'draft')
                <a href="{{ route('hr.expenses.edit', $expenseReport) }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Modifier
                </a>
            @endif
        </div>
    </div>

    <!-- Informations -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500 mb-1">Période</h3>
            <p class="text-lg font-semibold">
                {{ $expenseReport->period_start->format('d/m/Y') }} - {{ $expenseReport->period_end->format('d/m/Y') }}
            </p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500 mb-1">Montant total</h3>
            <p class="text-lg font-semibold text-primary-600">
                {{ number_format($expenseReport->total_amount, 2, ',', ' ') }} FCFA
            </p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500 mb-1">Soumis le</h3>
            <p class="text-lg font-semibold">
                {{ $expenseReport->submitted_at ? $expenseReport->submitted_at->format('d/m/Y H:i') : '-' }}
            </p>
        </div>
    </div>

    <!-- Lignes -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Détail des dépenses</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Justificatif</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($expenseReport->lines as $line)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            {{ $line->date->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded bg-gray-100">
                                {{ $line->category->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $line->description }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-medium">
                            {{ number_format($line->amount, 2, ',', ' ') }} FCFA
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($line->receipt_path)
                                <a href="{{ Storage::url($line->receipt_path) }}" target="_blank" class="text-primary-600 hover:underline text-sm">
                                    Voir
                                </a>
                            @else
                                <span class="text-gray-400 text-sm">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="3" class="px-6 py-4 text-right font-medium">Total</td>
                    <td class="px-6 py-4 text-right font-bold text-lg">
                        {{ number_format($expenseReport->total_amount, 2, ',', ' ') }} FCFA
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Notes -->
    @if($expenseReport->notes)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-2">Notes</h2>
            <p class="text-gray-600">{{ $expenseReport->notes }}</p>
        </div>
    @endif

    <!-- Actions -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Actions</h2>
        <div class="flex flex-wrap gap-2">
            @if($expenseReport->status === 'draft')
                <button wire:click="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Soumettre pour approbation
                </button>
            @endif

            @if($expenseReport->status === 'submitted')
                <button wire:click="approve" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Approuver
                </button>
                <button wire:click="reject" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Rejeter
                </button>
            @endif

            @if($expenseReport->status === 'approved')
                <button wire:click="markPaid" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                    Marquer comme payé
                </button>
            @endif

            <a href="{{ route('hr.expenses.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                Retour à la liste
            </a>

            @if(in_array($expenseReport->status, ['draft', 'rejected']))
                <button wire:click="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Supprimer
                </button>
            @endif
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-xl">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Confirmer la suppression</h3>
                        <p class="text-gray-500 text-sm">Cette action est irréversible</p>
                    </div>
                </div>
                <p class="text-gray-600 mb-6">
                    Êtes-vous sûr de vouloir supprimer la note de frais <strong>{{ $expenseReport->reference }}</strong> ?
                </p>
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Annuler
                    </button>
                    <button wire:click="deleteExpenseReport" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
