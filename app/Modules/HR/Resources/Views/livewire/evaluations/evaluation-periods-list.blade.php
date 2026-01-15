<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Campagnes d'évaluation</h1>
        <p class="text-gray-600 dark:text-gray-400">Gestion des périodes et campagnes d'évaluation</p>
    </div>

    <!-- Filtres et Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Rechercher..."
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">

            <select wire:model.live="yearFilter"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                @for($y = date('Y') + 1; $y >= 2020; $y--)
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
            Nouvelle campagne
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
                            @if($period->status === 'draft') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                            @elseif($period->status === 'open') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                            @elseif($period->status === 'closed') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                            @elseif($period->status === 'archived') bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400
                            @endif">
                            {{ $statuses[$period->status] ?? $period->status }}
                        </span>
                    </div>

                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex justify-between">
                            <span>Type:</span>
                            <span>{{ $types[$period->type] ?? $period->type }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Période:</span>
                            <span>{{ $period->start_date->format('d/m') }} - {{ $period->end_date->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Template:</span>
                            <span>{{ $period->template?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Évaluations:</span>
                            <span class="font-medium">{{ $period->evaluations_count }}</span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900 flex flex-wrap gap-2">
                    <a href="{{ route('hr.evaluations.periods.show', $period) }}"
                       class="text-sm text-primary-600 hover:text-primary-800 font-medium">
                        Voir détails
                    </a>

                    @if($period->status === 'draft')
                        <span class="text-gray-300">|</span>
                        <button wire:click="launchCampaign({{ $period->id }})"
                                wire:confirm="Lancer cette campagne d'évaluation? Des évaluations seront créées pour tous les employés éligibles."
                                class="text-sm text-green-600 hover:text-green-800 font-medium">
                            Lancer
                        </button>
                        <span class="text-gray-300">|</span>
                        <button wire:click="deletePeriod({{ $period->id }})"
                                wire:confirm="Supprimer cette période?"
                                class="text-sm text-red-600 hover:text-red-800 font-medium">
                            Supprimer
                        </button>
                    @elseif($period->status === 'open')
                        <span class="text-gray-300">|</span>
                        <button wire:click="closePeriod({{ $period->id }})"
                                wire:confirm="Clôturer cette campagne? Aucune nouvelle évaluation ne pourra être soumise."
                                class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Clôturer
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 bg-white dark:bg-gray-800 rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucune campagne</h3>
                <p class="mt-1 text-sm text-gray-500">Créez une nouvelle campagne d'évaluation.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $periods->links() }}
    </div>

    <!-- Modal Création -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50" wire:click="$set('showCreateModal', false)"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Nouvelle campagne d'évaluation</h3>
                    </div>

                    <form wire:submit="createPeriod" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom</label>
                            <input type="text" wire:model="periodForm.name"
                                   placeholder="Ex: Évaluation annuelle 2026"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('periodForm.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                                <select wire:model="periodForm.type"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @foreach($types as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Année</label>
                                <select wire:model="periodForm.year"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @for($y = date('Y') + 1; $y >= 2020; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
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

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Template d'évaluation</label>
                            <select wire:model="periodForm.evaluation_template_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Sélectionner un template</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                                @endforeach
                            </select>
                            @error('periodForm.evaluation_template_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" wire:click="$set('showCreateModal', false)"
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                Annuler
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                                Créer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
