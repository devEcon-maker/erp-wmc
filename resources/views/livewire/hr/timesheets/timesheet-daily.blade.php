<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h1 class="text-2xl font-bold text-white">Feuille de Temps</h1>
            <p class="text-text-secondary text-sm">Vue journaliere</p>
        </div>
        <div class="flex gap-2">
            <x-ui.button href="{{ route('hr.timesheets.weekly') }}" type="secondary">
                <span class="material-symbols-outlined text-[18px] mr-1">date_range</span>
                Vue Hebdomadaire
            </x-ui.button>
        </div>
    </div>

    @if(!$employeeId)
        <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
            <div class="flex items-center gap-3 p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-lg">
                <span class="material-symbols-outlined text-yellow-400">warning</span>
                <p class="text-yellow-400">
                    Vous n'etes pas associe a un profil employe. Contactez l'administrateur.
                </p>
            </div>
        </x-ui.card>
    @else
        <!-- Navigation date -->
        <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
            <div class="flex justify-between items-center">
                <button wire:click="previousDay" class="p-3 hover:bg-surface-highlight rounded-lg transition-colors text-text-secondary hover:text-white">
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>
                <div class="text-center">
                    <h2 class="text-lg font-semibold text-white capitalize">
                        {{ \Carbon\Carbon::parse($date)->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                    </h2>
                    @if(\Carbon\Carbon::parse($date)->isToday())
                        <span class="inline-flex items-center gap-1 text-sm text-primary mt-1">
                            <span class="material-symbols-outlined text-[14px]">today</span>
                            Aujourd'hui
                        </span>
                    @else
                        <button wire:click="today" class="text-sm text-primary hover:text-primary/80 transition-colors mt-1">
                            <span class="material-symbols-outlined text-[14px] align-middle mr-1">calendar_today</span>
                            Retour a aujourd'hui
                        </button>
                    @endif
                </div>
                <button wire:click="nextDay" class="p-3 hover:bg-surface-highlight rounded-lg transition-colors {{ \Carbon\Carbon::parse($date)->isToday() ? 'text-text-muted cursor-not-allowed' : 'text-text-secondary hover:text-white' }}" {{ \Carbon\Carbon::parse($date)->isToday() ? 'disabled' : '' }}>
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>
            </div>
        </x-ui.card>

        <!-- Stats rapides -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary">schedule</span>
                    </div>
                    <div>
                        <p class="text-sm text-text-secondary">Heures travaillees</p>
                        <p class="text-2xl font-bold text-white">{{ $totalHours }}h</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-green-400">payments</span>
                    </div>
                    <div>
                        <p class="text-sm text-text-secondary">Heures facturables</p>
                        <p class="text-2xl font-bold text-white">{{ $entries->where('billable', true)->sum('hours') }}h</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-blue-400">list_alt</span>
                    </div>
                    <div>
                        <p class="text-sm text-text-secondary">Entrees</p>
                        <p class="text-2xl font-bold text-white">{{ count($entries) }}</p>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <!-- Formulaire d'ajout -->
        <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
            <h3 class="text-lg font-bold text-white mb-4 border-b border-[#3a2e24] pb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">add_circle</span>
                Ajouter une entree
            </h3>
            <form wire:submit="addEntry">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <x-ui.select wire:model="project_id" label="Projet" required>
                            <option value="">Selectionner un projet...</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </x-ui.select>
                        @error('project_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <x-ui.input
                            type="number"
                            step="0.25"
                            min="0.25"
                            max="24"
                            wire:model="hours"
                            label="Heures"
                            required
                            placeholder="Ex: 2.5" />
                        @error('hours') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <x-ui.input
                            type="text"
                            wire:model="description"
                            label="Description"
                            placeholder="Travail effectue..." />
                    </div>
                    <div class="flex items-end gap-4">
                        <label class="flex items-center gap-2 cursor-pointer mb-4">
                            <input type="checkbox" wire:model="billable"
                                class="form-checkbox h-5 w-5 rounded border-[#3a2e24] bg-input-dark text-primary focus:ring-primary">
                            <span class="text-sm text-white">Facturable</span>
                        </label>
                        <x-ui.button type="primary" submit class="mb-4">
                            <span class="material-symbols-outlined text-[18px] mr-1">add</span>
                            Ajouter
                        </x-ui.button>
                    </div>
                </div>
            </form>
        </x-ui.card>

        <!-- Entrées du jour -->
        <x-ui.card class="bg-surface-dark border border-[#3a2e24] p-0 overflow-hidden">
            <div class="px-6 py-4 border-b border-[#3a2e24]">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">list</span>
                    Entrees du jour
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-surface-highlight">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Projet</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-text-secondary uppercase tracking-wider">Heures</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-text-secondary uppercase tracking-wider">Facturable</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-text-secondary uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#3a2e24]">
                        @forelse($entries as $entry)
                            <tr class="hover:bg-surface-highlight/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-primary text-[16px]">folder</span>
                                        </div>
                                        <span class="text-sm font-medium text-white">
                                            {{ $entry->project->name ?? 'Projet #' . ($entry->project_id ?? '-') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-text-secondary">{{ $entry->description ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="text-lg font-bold text-primary">{{ $entry->hours }}h</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($entry->billable)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-400">
                                            <span class="material-symbols-outlined text-[14px] mr-1">check</span>
                                            Oui
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-500/20 text-gray-400">
                                            <span class="material-symbols-outlined text-[14px] mr-1">close</span>
                                            Non
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    @if(!$entry->approved)
                                        <button wire:click="deleteEntry({{ $entry->id }})"
                                            class="inline-flex items-center gap-1 text-red-400 hover:text-red-300 text-sm transition-colors">
                                            <span class="material-symbols-outlined text-[16px]">delete</span>
                                            Supprimer
                                        </button>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-green-400 text-xs">
                                            <span class="material-symbols-outlined text-[14px]">verified</span>
                                            Approuve
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="material-symbols-outlined text-5xl text-text-muted mb-3">hourglass_empty</span>
                                        <p class="text-text-secondary">Aucune entree pour cette journee.</p>
                                        <p class="text-text-muted text-sm mt-1">Ajoutez-en une via le formulaire ci-dessus.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        @if(empty($projects) || count($projects) === 0)
            <div class="flex items-center gap-3 p-4 bg-amber-500/10 border border-amber-500/30 rounded-lg">
                <span class="material-symbols-outlined text-amber-400">info</span>
                <p class="text-amber-400 text-sm">
                    Note: Aucun projet disponible. Les projets doivent etre crees dans le module Productivite.
                </p>
            </div>
        @endif
    @endif
</div>
