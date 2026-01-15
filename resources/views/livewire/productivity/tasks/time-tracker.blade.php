<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white">Time Tracker</h2>
            <p class="text-text-secondary text-sm">Suivez votre temps de travail.</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="openEntryModal"
                class="px-4 py-2 bg-surface-highlight text-white rounded-lg hover:bg-surface-highlight/80 flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Entrée manuelle
            </button>
        </div>
    </div>

    <!-- Timer -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center gap-4">
            <!-- Timer Display -->
            <div class="flex-1">
                @if($isTracking)
                    <div class="flex items-center gap-4" x-data="{ elapsed: 0 }" x-init="
                        const start = new Date('{{ $trackingStart }}');
                        setInterval(() => {
                            elapsed = Math.floor((new Date() - start) / 1000);
                        }, 1000);
                    ">
                        <div class="w-12 h-12 rounded-full bg-green-500/20 flex items-center justify-center animate-pulse">
                            <span class="material-symbols-outlined text-green-400">timer</span>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-white font-mono" x-text="
                                Math.floor(elapsed / 3600).toString().padStart(2, '0') + ':' +
                                Math.floor((elapsed % 3600) / 60).toString().padStart(2, '0') + ':' +
                                (elapsed % 60).toString().padStart(2, '0')
                            ">00:00:00</p>
                            <p class="text-sm text-text-secondary">En cours...</p>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-surface-highlight flex items-center justify-center">
                            <span class="material-symbols-outlined text-text-secondary">timer</span>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-white font-mono">00:00:00</p>
                            <p class="text-sm text-text-secondary">Prêt à démarrer</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Timer Controls -->
            <div class="flex flex-wrap items-center gap-3">
                @if(!$isTracking)
                    <x-ui.select wire:model.live="trackingProjectId" class="w-48" :error="$errors->first('trackingProjectId')">
                        <option value="">Sélectionner un projet</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </x-ui.select>

                    @if($trackingProjectId)
                        <x-ui.select wire:model="trackingTaskId" class="w-48">
                            <option value="">Tâche (optionnel)</option>
                            @foreach($tasksForProject as $task)
                                <option value="{{ $task->id }}">{{ Str::limit($task->title, 25) }}</option>
                            @endforeach
                        </x-ui.select>
                    @endif

                    <x-ui.input
                        type="text"
                        wire:model="trackingDescription"
                        placeholder="Description..."
                        class="w-48"
                    />

                    <button wire:click="startTracking"
                        class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">play_arrow</span>
                        Démarrer
                    </button>
                @else
                    <button wire:click="stopTracking"
                        class="px-6 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">stop</span>
                        Arrêter
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">Total heures</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($totalHours, 1) }}h</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">schedule</span>
                </div>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">Facturables</p>
                    <p class="text-2xl font-bold text-green-400">{{ number_format($billableHours, 1) }}h</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-green-400">payments</span>
                </div>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">Non facturables</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ number_format($totalHours - $billableHours, 1) }}h</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-yellow-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-yellow-400">money_off</span>
                </div>
            </div>
        </div>
    </div>

    <!-- View Toggle & Navigation -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-2 bg-surface-dark border border-[#3a2e24] rounded-lg p-1">
            <button wire:click="$set('viewMode', 'day')"
                class="px-4 py-2 rounded-md text-sm transition-colors {{ $viewMode === 'day' ? 'bg-primary text-white' : 'text-text-secondary hover:text-white' }}">
                Jour
            </button>
            <button wire:click="$set('viewMode', 'week')"
                class="px-4 py-2 rounded-md text-sm transition-colors {{ $viewMode === 'week' ? 'bg-primary text-white' : 'text-text-secondary hover:text-white' }}">
                Semaine
            </button>
        </div>

        <div class="flex items-center gap-2">
            @if($viewMode === 'day')
                <button wire:click="previousDay" class="p-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg">
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>
                <span class="text-white font-medium min-w-[150px] text-center">
                    {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l d M Y') }}
                </span>
                <button wire:click="nextDay" class="p-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg">
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>
            @else
                <button wire:click="previousWeek" class="p-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg">
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>
                <span class="text-white font-medium min-w-[200px] text-center">
                    Semaine du {{ \Carbon\Carbon::parse($selectedWeek)->format('d/m/Y') }}
                </span>
                <button wire:click="nextWeek" class="p-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg">
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>
            @endif
            <button wire:click="goToToday" class="px-3 py-2 text-sm text-primary hover:bg-primary/10 rounded-lg">
                Aujourd'hui
            </button>
        </div>

        <div class="flex gap-2">
            <x-ui.select wire:model.live="projectId" class="w-48">
                <option value="">Tous les projets</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </x-ui.select>
        </div>
    </div>

    <!-- Day View -->
    @if($viewMode === 'day')
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl overflow-hidden">
            @if($entries->count() > 0)
                <div class="divide-y divide-[#3a2e24]">
                    @foreach($entries as $entry)
                        <div class="p-4 hover:bg-surface-highlight transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-primary/20 flex items-center justify-center text-sm font-medium text-primary">
                                        {{ number_format($entry->hours, 1) }}h
                                    </div>
                                    <div>
                                        <a href="{{ route('productivity.projects.show', $entry->project) }}"
                                            class="font-medium text-white hover:text-primary">
                                            {{ $entry->project->name }}
                                        </a>
                                        <div class="flex items-center gap-2 text-sm text-text-secondary">
                                            @if($entry->task)
                                                <span>{{ $entry->task->title }}</span>
                                                <span>·</span>
                                            @endif
                                            @if($entry->description)
                                                <span>{{ Str::limit($entry->description, 50) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    @if($entry->billable)
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-500/20 text-green-400">Facturable</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-500/20 text-gray-400">Non facturable</span>
                                    @endif
                                    @if($entry->approved === true)
                                        <span class="material-symbols-outlined text-green-400 text-[20px]">verified</span>
                                    @elseif($entry->approved === null)
                                        <button wire:click="deleteEntry({{ $entry->id }})"
                                            wire:confirm="Supprimer cette entrée ?"
                                            class="p-1 text-red-400 hover:text-red-300">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-12 text-center">
                    <span class="material-symbols-outlined text-5xl text-text-secondary mb-4">schedule</span>
                    <h3 class="text-lg font-medium text-white mb-2">Aucune entrée</h3>
                    <p class="text-text-secondary mb-4">Aucun temps enregistré pour cette période.</p>
                </div>
            @endif
        </div>
    @endif

    <!-- Week View -->
    @if($viewMode === 'week')
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl overflow-hidden">
            <div class="grid grid-cols-7 divide-x divide-[#3a2e24]">
                @foreach($weekDays as $day)
                    <div class="min-h-[200px] {{ $day['isWeekend'] ? 'bg-surface-highlight/30' : '' }}">
                        <div class="p-2 border-b border-[#3a2e24] text-center {{ $day['isToday'] ? 'bg-primary/20' : '' }}">
                            <p class="text-xs text-text-secondary">{{ $day['date']->translatedFormat('D') }}</p>
                            <p class="text-lg font-medium {{ $day['isToday'] ? 'text-primary' : 'text-white' }}">
                                {{ $day['date']->format('d') }}
                            </p>
                            @if($day['total'] > 0)
                                <p class="text-xs text-green-400">{{ number_format($day['total'], 1) }}h</p>
                            @endif
                        </div>
                        <div class="p-2 space-y-1">
                            @foreach($day['entries'] as $entry)
                                <div class="text-xs p-1 bg-primary/20 text-primary rounded truncate" title="{{ $entry->project->name }}">
                                    {{ number_format($entry->hours, 1) }}h - {{ Str::limit($entry->project->name, 10) }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Entry Modal -->
    @if($showEntryModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="closeEntryModal">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Entrée manuelle</h3>

                <div class="space-y-4">
                    <x-ui.select label="Projet *" wire:model.live="entryForm.project_id" :error="$errors->first('entryForm.project_id')">
                        <option value="">Sélectionner un projet</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </x-ui.select>

                    @if($entryForm['project_id'])
                        <x-ui.select label="Tâche" wire:model="entryForm.task_id">
                            <option value="">Sélectionner une tâche (optionnel)</option>
                            @foreach($tasksForProject as $task)
                                <option value="{{ $task->id }}">{{ $task->title }}</option>
                            @endforeach
                        </x-ui.select>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <x-ui.input
                            type="date"
                            label="Date *"
                            wire:model="entryForm.date"
                            :error="$errors->first('entryForm.date')"
                        />
                        <x-ui.input
                            type="number"
                            step="0.25"
                            label="Heures *"
                            wire:model="entryForm.hours"
                            placeholder="0"
                            :error="$errors->first('entryForm.hours')"
                        />
                    </div>

                    <x-ui.textarea
                        label="Description"
                        wire:model="entryForm.description"
                        placeholder="Description..."
                        rows="2"
                    />

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="entryForm.billable"
                            class="w-4 h-4 rounded bg-surface-highlight border-[#3a2e24] text-primary focus:ring-primary">
                        <span class="text-white">Facturable</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <x-ui.button type="secondary" wire:click="closeEntryModal">Annuler</x-ui.button>
                    <x-ui.button wire:click="saveEntry">Ajouter</x-ui.button>
                </div>
            </div>
        </div>
    @endif
</div>
