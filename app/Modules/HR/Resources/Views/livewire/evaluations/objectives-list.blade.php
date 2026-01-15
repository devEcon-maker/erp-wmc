<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Objectifs</h1>
        <p class="text-gray-600 dark:text-gray-400">Gestion des objectifs des employés</p>
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

            <select wire:model.live="yearFilter"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                @for($y = date('Y') + 1; $y >= 2020; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>

        <button wire:click="openCreateModal"
                class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouvel objectif
        </button>
    </div>

    <!-- Liste des objectifs -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Objectif</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Employé</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Échéance</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Progression</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($objectives as $objective)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $objective->title }}</div>
                                    <div class="text-sm text-gray-500">{{ Str::limit($objective->description, 50) }}</div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-600">
                                            {{ $categories[$objective->category] ?? $objective->category }}
                                        </span>
                                        <span class="px-2 py-0.5 text-xs rounded
                                            @if($objective->priority === 'critical') bg-red-100 text-red-600
                                            @elseif($objective->priority === 'high') bg-orange-100 text-orange-600
                                            @elseif($objective->priority === 'medium') bg-yellow-100 text-yellow-600
                                            @else bg-gray-100 text-gray-600
                                            @endif">
                                            {{ $priorities[$objective->priority] ?? $objective->priority }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $objective->employee->full_name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $objective->employee->department?->name ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="@if($objective->due_date->isPast() && $objective->status !== 'completed') text-red-600 font-medium @else text-gray-500 @endif">
                                    {{ $objective->due_date->format('d/m/Y') }}
                                </span>
                                @if($objective->due_date->isPast() && $objective->status !== 'completed')
                                    <br><span class="text-xs text-red-500">En retard</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 w-24">
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="h-2 rounded-full
                                                @if($objective->progress_percentage >= 75) bg-green-600
                                                @elseif($objective->progress_percentage >= 50) bg-blue-600
                                                @elseif($objective->progress_percentage >= 25) bg-yellow-600
                                                @else bg-gray-400
                                                @endif"
                                                 style="width: {{ $objective->progress_percentage }}%"></div>
                                        </div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $objective->progress_percentage }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($objective->status === 'pending') bg-gray-100 text-gray-800
                                    @elseif($objective->status === 'in_progress') bg-blue-100 text-blue-800
                                    @elseif($objective->status === 'completed') bg-green-100 text-green-800
                                    @elseif($objective->status === 'cancelled') bg-red-100 text-red-800
                                    @endif">
                                    {{ $statuses[$objective->status] ?? $objective->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex justify-end gap-2">
                                    @if($objective->status === 'pending')
                                        <button wire:click="startObjective({{ $objective->id }})"
                                                class="text-blue-600 hover:text-blue-800">
                                            Démarrer
                                        </button>
                                    @elseif($objective->status === 'in_progress')
                                        <button wire:click="openProgressModal({{ $objective->id }})"
                                                class="text-primary-600 hover:text-primary-800">
                                            Progression
                                        </button>
                                        <button wire:click="completeObjective({{ $objective->id }})"
                                                class="text-green-600 hover:text-green-800">
                                            Compléter
                                        </button>
                                    @endif
                                    @if($objective->status !== 'completed' && $objective->status !== 'cancelled')
                                        <button wire:click="cancelObjective({{ $objective->id }})"
                                                class="text-red-600 hover:text-red-800">
                                            Annuler
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                Aucun objectif trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $objectives->links() }}
    </div>

    <!-- Modal Création -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50" wire:click="$set('showCreateModal', false)"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Nouvel objectif</h3>
                    </div>

                    <form wire:submit="createObjective" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employé</label>
                            <select wire:model="objectiveForm.employee_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Sélectionner un employé</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                            @error('objectiveForm.employee_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Titre</label>
                            <input type="text" wire:model="objectiveForm.title"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('objectiveForm.title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <textarea wire:model="objectiveForm.description" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catégorie</label>
                                <select wire:model="objectiveForm.category"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priorité</label>
                                <select wire:model="objectiveForm.priority"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @foreach($priorities as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date début</label>
                                <input type="date" wire:model="objectiveForm.start_date"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Échéance</label>
                                <input type="date" wire:model="objectiveForm.due_date"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valeur cible</label>
                                <input type="number" wire:model="objectiveForm.target_value" step="0.01"
                                       placeholder="Optionnel"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unité</label>
                                <input type="text" wire:model="objectiveForm.unit"
                                       placeholder="Ex: ventes, %, projets..."
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
                                Créer l'objectif
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Progression -->
    @if($showProgressModal && $selectedObjective)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50" wire:click="$set('showProgressModal', false)"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Mettre à jour la progression</h3>
                    </div>

                    <div class="p-6">
                        <div class="mb-4">
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $selectedObjective->title }}</h4>
                            <p class="text-sm text-gray-500">{{ $selectedObjective->employee->full_name }}</p>
                        </div>

                        @if($selectedObjective->target_value)
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Valeur actuelle (cible: {{ $selectedObjective->target_value }} {{ $selectedObjective->unit }})
                                </label>
                                <input type="number" wire:model="progressValue" step="0.01"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                        @else
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Progression (%)
                                </label>
                                <input type="range" wire:model.live="progressValue" min="0" max="100" step="5"
                                       class="w-full">
                                <div class="text-center text-2xl font-bold text-primary-600 mt-2">{{ $progressValue }}%</div>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                            <textarea wire:model="progressNotes" rows="3"
                                      placeholder="Notes sur la progression..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        </div>

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
