<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Périodes de Paie</h1>
        <p class="text-gray-600 dark:text-gray-400">Gestion des périodes de paie et génération des bulletins</p>
    </div>

    <!-- Filtres et Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Rechercher..."
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">

            <select wire:model.live="yearFilter"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">Toutes les années</option>
                @for($y = date('Y'); $y >= 2020; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>

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
            Nouvelle période
        </button>
    </div>

    <!-- Liste des périodes -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($periods as $period)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $period->name }}</h3>
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            @if($period->status === 'draft') bg-gray-100 text-gray-800
                            @elseif($period->status === 'validated') bg-blue-100 text-blue-800
                            @elseif($period->status === 'paid') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ $statuses[$period->status] ?? $period->status }}
                        </span>
                    </div>

                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex justify-between">
                            <span>Période:</span>
                            <span>{{ $period->start_date->format('d/m') }} - {{ $period->end_date->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Jours travaillés:</span>
                            <span>{{ $period->working_days ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Bulletins:</span>
                            <span class="font-medium">{{ $period->payslips_count }}</span>
                        </div>
                        @if($period->total_net_salary)
                            <div class="flex justify-between font-semibold text-gray-900 dark:text-white">
                                <span>Total net:</span>
                                <span>{{ number_format($period->total_net_salary, 0, ',', ' ') }} FCFA</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900 flex flex-wrap gap-2">
                    <a href="{{ route('hr.payroll.periods.show', $period) }}"
                       class="text-sm text-primary-600 hover:text-primary-800 font-medium">
                        Voir détails
                    </a>

                    @if($period->status === 'draft')
                        <span class="text-gray-300">|</span>
                        <button wire:click="generatePayslips({{ $period->id }})"
                                wire:confirm="Générer tous les bulletins de paie pour cette période?"
                                class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Générer bulletins
                        </button>
                        <span class="text-gray-300">|</span>
                        <button wire:click="validatePeriod({{ $period->id }})"
                                wire:confirm="Valider cette période? Cette action est irréversible."
                                class="text-sm text-green-600 hover:text-green-800 font-medium">
                            Valider
                        </button>
                        <span class="text-gray-300">|</span>
                        <button wire:click="deletePeriod({{ $period->id }})"
                                wire:confirm="Supprimer cette période et tous ses bulletins?"
                                class="text-sm text-red-600 hover:text-red-800 font-medium">
                            Supprimer
                        </button>
                    @elseif($period->status === 'validated')
                        <span class="text-gray-300">|</span>
                        <button wire:click="markAsPaid({{ $period->id }})"
                                wire:confirm="Marquer cette période comme payée?"
                                class="text-sm text-green-600 hover:text-green-800 font-medium">
                            Marquer payée
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 bg-white dark:bg-gray-800 rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucune période de paie</h3>
                <p class="mt-1 text-sm text-gray-500">Créez une nouvelle période pour commencer.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $periods->links() }}
    </div>

    <!-- Modal Création -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data x-init="document.body.classList.add('overflow-hidden')" x-on:remove="document.body.classList.remove('overflow-hidden')">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50" wire:click="$set('showCreateModal', false)"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Nouvelle période de paie</h3>
                    </div>

                    <form wire:submit="createPeriod" class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mois</label>
                                <select wire:model.live="periodForm.month"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @foreach(['01' => 'Janvier', '02' => 'Février', '03' => 'Mars', '04' => 'Avril', '05' => 'Mai', '06' => 'Juin', '07' => 'Juillet', '08' => 'Août', '09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre'] as $num => $name)
                                        <option value="{{ $num }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('periodForm.month') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Année</label>
                                <select wire:model.live="periodForm.year"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @for($y = date('Y') + 1; $y >= 2020; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                                @error('periodForm.year') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom de la période</label>
                            <input type="text" wire:model="periodForm.name"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('periodForm.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date début</label>
                                <input type="date" wire:model="periodForm.start_date"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date fin</label>
                                <input type="date" wire:model="periodForm.end_date"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" wire:click="$set('showCreateModal', false)"
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                Annuler
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                                Créer la période
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
