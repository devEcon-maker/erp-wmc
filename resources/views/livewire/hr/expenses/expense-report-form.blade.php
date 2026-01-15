<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">
            {{ $expenseReport ? 'Modifier la note de frais' : 'Nouvelle note de frais' }}
        </h1>
        <p class="text-text-secondary mt-1">Ajoutez vos dépenses professionnelles pour remboursement.</p>
    </div>

    @error('employee')
        <div class="mb-4 p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400">
            {{ $message }}
        </div>
    @enderror

    <form wire:submit="save">
        <!-- Période -->
        <div class="bg-surface-dark border border-white/10 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-white mb-4">Période de la note</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-1">Date début *</label>
                    <input type="date" wire:model="period_start"
                        class="w-full bg-surface-dark border border-white/10 rounded-lg text-white py-2 px-3 focus:ring-primary-500 focus:border-primary-500">
                    @error('period_start') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-1">Date fin *</label>
                    <input type="date" wire:model="period_end"
                        class="w-full bg-surface-dark border border-white/10 rounded-lg text-white py-2 px-3 focus:ring-primary-500 focus:border-primary-500">
                    @error('period_end') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Ajouter une dépense -->
        <div class="bg-surface-dark border border-white/10 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-white mb-4">Ajouter une dépense</h2>

            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                    <label class="block text-xs font-medium text-text-secondary mb-1">Catégorie *</label>
                    <select wire:model="newLine.category_id"
                        class="w-full bg-surface-dark border border-white/10 rounded-lg text-white py-2 px-3 text-sm">
                        <option value="">Choisir...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->name }}
                                @if($category->max_amount) (max {{ $category->max_amount }}FCFA) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('newLine.category_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-text-secondary mb-1">Date *</label>
                    <input type="date" wire:model="newLine.date"
                        class="w-full bg-surface-dark border border-white/10 rounded-lg text-white py-2 px-3 text-sm">
                    @error('newLine.date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-text-secondary mb-1">Description *</label>
                    <input type="text" wire:model="newLine.description" placeholder="Ex: Déjeuner client"
                        class="w-full bg-surface-dark border border-white/10 rounded-lg text-white py-2 px-3 text-sm">
                    @error('newLine.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-text-secondary mb-1">Montant (FCFA) *</label>
                    <input type="number" step="0.01" wire:model="newLine.amount" placeholder="0.00"
                        class="w-full bg-surface-dark border border-white/10 rounded-lg text-white py-2 px-3 text-sm">
                    @error('newLine.amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-text-secondary mb-1">Justificatif</label>
                    <input type="file" wire:model="newLine.receipt"
                        class="w-full text-xs text-text-secondary file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-primary/20 file:text-primary">
                    @error('newLine.receipt') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="button" wire:click="addLine"
                    class="px-4 py-2 bg-primary hover:bg-primary/90 text-white font-medium rounded-lg text-sm">
                    <span class="material-symbols-outlined text-[18px] align-middle mr-1">add</span>
                    Ajouter la dépense
                </button>
            </div>
        </div>

        <!-- Liste des dépenses -->
        <div class="bg-surface-dark border border-white/10 rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-white/10 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-white">Dépenses ajoutées</h2>
                <span class="text-text-secondary">{{ count($lines) }} ligne(s)</span>
            </div>

            @error('lines') <div class="px-6 py-2 text-red-500 text-sm">{{ $message }}</div> @enderror

            @if(count($lines) > 0)
                <table class="w-full">
                    <thead class="bg-white/5">
                        <tr class="text-text-secondary text-xs uppercase">
                            <th class="py-3 px-4 text-left">Date</th>
                            <th class="py-3 px-4 text-left">Catégorie</th>
                            <th class="py-3 px-4 text-left">Description</th>
                            <th class="py-3 px-4 text-right">Montant</th>
                            <th class="py-3 px-4 text-center">Justif.</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($lines as $index => $line)
                            <tr class="hover:bg-white/5">
                                <td class="py-3 px-4 text-white text-sm">
                                    {{ \Carbon\Carbon::parse($line['date'])->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4 text-white text-sm">
                                    {{ $categories->find($line['category_id'])?->name ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-text-secondary text-sm">
                                    {{ $line['description'] }}
                                </td>
                                <td class="py-3 px-4 text-white text-sm text-right font-medium">
                                    {{ number_format($line['amount'], 2, ',', ' ') }} FCFA
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($line['receipt_path'] ?? false)
                                        <span class="text-green-500">✓</span>
                                    @else
                                        <span class="text-text-secondary">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <button type="button" wire:click="removeLine({{ $index }})"
                                        class="text-red-400 hover:text-red-300 text-sm">
                                        Supprimer
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-white/5">
                        <tr>
                            <td colspan="3" class="py-3 px-4 text-right font-semibold text-white">Total</td>
                            <td class="py-3 px-4 text-right font-bold text-xl text-primary">
                                {{ number_format($total, 2, ',', ' ') }} FCFA
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            @else
                <div class="p-8 text-center text-text-secondary">
                    <span class="material-symbols-outlined text-4xl mb-2">receipt_long</span>
                    <p>Aucune dépense ajoutée. Utilisez le formulaire ci-dessus.</p>
                </div>
            @endif
        </div>

        <!-- Actions -->
        <div class="flex justify-between">
            <a href="{{ route('hr.expenses.index') }}"
                class="px-4 py-2 border border-white/10 text-text-secondary rounded-lg hover:bg-white/5">
                Annuler
            </a>
            <button type="submit"
                class="px-6 py-2 bg-primary hover:bg-primary/90 text-white font-bold rounded-lg"
                {{ count($lines) === 0 ? 'disabled' : '' }}>
                Enregistrer la note de frais
            </button>
        </div>
    </form>
</div>
