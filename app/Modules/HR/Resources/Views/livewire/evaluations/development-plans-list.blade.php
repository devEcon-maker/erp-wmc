<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Plans de développement</h1>
        <p class="text-gray-600 dark:text-gray-400">Gestion des plans de développement des compétences</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">Total</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">En cours</div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['in_progress'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">Terminés</div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">En retard</div>
            <div class="text-2xl font-bold text-red-600">{{ $stats['overdue'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">Budget prévu</div>
            <div class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_budget'], 0, ',', ' ') }}</div>
            <div class="text-xs text-gray-500">FCFA</div>
        </div>
    </div>

    <!-- Filtres et Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Rechercher..."
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">

            <select wire:model.live="statusFilter"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">Tous les statuts</option>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            <select wire:model.live="skillAreaFilter"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">Tous les domaines</option>
                @foreach($skillAreas as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <button wire:click="openCreateModal"
                class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouveau plan
        </button>
    </div>

    <!-- Liste des plans -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($plans as $plan)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden
                @if($plan->is_overdue) border-l-4 border-l-red-500 @endif">
                <div class="p-4">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $plan->title }}</h3>
                            <p class="text-sm text-gray-500">{{ $plan->employee->full_name }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $plan->status_color }}-100 text-{{ $plan->status_color }}-800">
                            {{ $statuses[$plan->status] ?? $plan->status }}
                        </span>
                    </div>

                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ Str::limit($plan->description, 80) }}</p>

                    <div class="flex flex-wrap gap-2 mb-3">
                        <span class="px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            {{ $skillAreas[$plan->skill_area] ?? $plan->skill_area }}
                        </span>
                        <span class="px-2 py-0.5 text-xs rounded bg-blue-100 text-blue-600">
                            {{ $actionTypes[$plan->action_type] ?? $plan->action_type }}
                        </span>
                    </div>

                    <!-- Barre de progression -->
                    <div class="mb-3">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Progression</span>
                            <span>{{ $plan->progress_percentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full bg-{{ $plan->progress_color }}-600" style="width: {{ $plan->progress_percentage }}%"></div>
                        </div>
                    </div>

                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        <div class="flex justify-between">
                            <span>Début:</span>
                            <span>{{ $plan->start_date->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Échéance:</span>
                            <span class="@if($plan->is_overdue) text-red-600 font-medium @endif">
                                {{ $plan->target_date->format('d/m/Y') }}
                            </span>
                        </div>
                        @if($plan->budget)
                            <div class="flex justify-between">
                                <span>Budget:</span>
                                <span>{{ number_format($plan->budget, 0, ',', ' ') }} FCFA</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900 flex flex-wrap gap-2">
                    @if($plan->status === 'planned')
                        <button wire:click="startPlan({{ $plan->id }})"
                                class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Démarrer
                        </button>
                    @elseif($plan->status === 'in_progress')
                        <button wire:click="openProgressModal({{ $plan->id }})"
                                class="text-sm text-primary-600 hover:text-primary-800 font-medium">
                            Progression
                        </button>
                        <span class="text-gray-300">|</span>
                        <button wire:click="completePlan({{ $plan->id }})"
                                class="text-sm text-green-600 hover:text-green-800 font-medium">
                            Terminer
                        </button>
                    @endif

                    @if($plan->status !== 'completed' && $plan->status !== 'cancelled')
                        <span class="text-gray-300">|</span>
                        <button wire:click="cancelPlan({{ $plan->id }})"
                                class="text-sm text-red-600 hover:text-red-800 font-medium">
                            Annuler
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 bg-white dark:bg-gray-800 rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucun plan de développement</h3>
                <p class="mt-1 text-sm text-gray-500">Créez un nouveau plan pour commencer.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $plans->links() }}
    </div>

    <!-- Modal Création -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50" wire:click="$set('showCreateModal', false)"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Nouveau plan de développement</h3>
                    </div>

                    <form wire:submit="createPlan" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employé</label>
                            <select wire:model="planForm.employee_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Sélectionner un employé</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                            @error('planForm.employee_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Titre</label>
                            <input type="text" wire:model="planForm.title"
                                   placeholder="Ex: Formation React avancé"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('planForm.title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <textarea wire:model="planForm.description" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Domaine</label>
                                <select wire:model="planForm.skill_area"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @foreach($skillAreas as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type d'action</label>
                                <select wire:model="planForm.action_type"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @foreach($actionTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date début</label>
                                <input type="date" wire:model="planForm.start_date"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Échéance</label>
                                <input type="date" wire:model="planForm.target_date"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ressources nécessaires</label>
                            <textarea wire:model="planForm.resources_needed" rows="2"
                                      placeholder="Matériel, outils, accès..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Budget (FCFA)</label>
                            <input type="number" wire:model="planForm.budget" min="0" step="1000"
                                   placeholder="Optionnel"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" wire:click="$set('showCreateModal', false)"
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                Annuler
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                                Créer le plan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Progression -->
    @if($showProgressModal && $selectedPlan)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50" wire:click="$set('showProgressModal', false)"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Mettre à jour la progression</h3>
                    </div>

                    <div class="p-6">
                        <div class="mb-4">
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $selectedPlan->title }}</h4>
                            <p class="text-sm text-gray-500">{{ $selectedPlan->employee->full_name }}</p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Progression (%)
                            </label>
                            <input type="range" wire:model.live="progressPercentage" min="0" max="100" step="5"
                                   class="w-full">
                            <div class="text-center text-2xl font-bold text-primary-600 mt-2">{{ $progressPercentage }}%</div>
                        </div>

                        @if($progressPercentage >= 100)
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes de complétion</label>
                                <textarea wire:model="completionNotes" rows="3"
                                          placeholder="Résumé des acquis, certifications obtenues..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                            </div>
                        @endif

                        <div class="flex justify-end gap-3">
                            <button wire:click="$set('showProgressModal', false)"
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                Annuler
                            </button>
                            <button wire:click="updateProgress"
                                    class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                                Mettre à jour
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
