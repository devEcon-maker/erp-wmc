<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h1 class="text-2xl font-bold text-white">Feuille de Temps</h1>
            <p class="text-text-secondary text-sm">Vue hebdomadaire</p>
        </div>
        <div class="flex gap-2">
            <x-ui.button href="{{ route('hr.timesheets.daily') }}" type="secondary">
                <span class="material-symbols-outlined text-[18px] mr-1">today</span>
                Vue Journaliere
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
        <!-- Navigation semaine -->
        <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
            <div class="flex justify-between items-center">
                <button wire:click="previousWeek" class="p-3 hover:bg-surface-highlight rounded-lg transition-colors text-text-secondary hover:text-white">
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>
                <div class="text-center">
                    <h2 class="text-lg font-semibold text-white">
                        Semaine du {{ \Carbon\Carbon::parse($weekStart)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($weekStart)->addDays(6)->format('d/m/Y') }}
                    </h2>
                    <button wire:click="currentWeek" class="text-sm text-primary hover:text-primary/80 transition-colors mt-1">
                        <span class="material-symbols-outlined text-[14px] align-middle mr-1">calendar_today</span>
                        Semaine actuelle
                    </button>
                </div>
                <button wire:click="nextWeek" class="p-3 hover:bg-surface-highlight rounded-lg transition-colors text-text-secondary hover:text-white">
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>
            </div>
        </x-ui.card>

        <!-- Grille de la semaine -->
        <x-ui.card class="bg-surface-dark border border-[#3a2e24] p-0 overflow-hidden">
            <div class="grid grid-cols-8 divide-x divide-[#3a2e24]">
                <!-- En-tetes -->
                @foreach($weekDays as $day)
                    <div class="p-4 text-center border-b border-[#3a2e24] {{ $day['isToday'] ? 'bg-primary/10' : '' }} {{ $day['isWeekend'] ? 'bg-surface-highlight/50' : '' }}">
                        <div class="text-xs font-medium text-text-secondary uppercase tracking-wide">{{ ucfirst($day['day']) }}</div>
                        <div class="text-xl font-bold text-white mt-1">{{ $day['number'] }}</div>
                    </div>
                @endforeach
                <div class="p-4 text-center border-b border-[#3a2e24] bg-surface-highlight">
                    <div class="text-xs font-medium text-text-secondary uppercase tracking-wide">Total</div>
                    <div class="text-xl font-bold text-white mt-1">Sem.</div>
                </div>

                <!-- Valeurs -->
                @foreach($weekDays as $day)
                    @php
                        $dayEntry = $entries[$day['date']] ?? ['hours' => 0];
                    @endphp
                    <div class="p-6 text-center {{ $day['isToday'] ? 'bg-primary/10' : '' }} {{ $day['isWeekend'] ? 'bg-surface-highlight/50' : '' }}">
                        <div class="text-3xl font-bold {{ $dayEntry['hours'] > 0 ? 'text-primary' : 'text-text-muted' }}">
                            {{ $dayEntry['hours'] ?: '-' }}
                        </div>
                        @if($dayEntry['hours'] > 0)
                            <div class="text-xs text-text-secondary mt-1">heures</div>
                        @endif
                    </div>
                @endforeach
                <div class="p-6 text-center bg-surface-highlight">
                    <div class="text-3xl font-bold {{ $totalWeekHours >= 35 ? 'text-green-400' : 'text-amber-400' }}">
                        {{ $totalWeekHours }}h
                    </div>
                    <div class="text-xs text-text-secondary mt-1">/ 35h</div>
                </div>
            </div>
        </x-ui.card>

        <!-- Stats rapides -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary">schedule</span>
                    </div>
                    <div>
                        <p class="text-sm text-text-secondary">Heures cette semaine</p>
                        <p class="text-2xl font-bold text-white">{{ $totalWeekHours }}h</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-green-400">target</span>
                    </div>
                    <div>
                        <p class="text-sm text-text-secondary">Objectif hebdo</p>
                        <p class="text-2xl font-bold text-white">35h</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl {{ $totalWeekHours >= 35 ? 'bg-green-500/20' : 'bg-amber-500/20' }} flex items-center justify-center">
                        <span class="material-symbols-outlined {{ $totalWeekHours >= 35 ? 'text-green-400' : 'text-amber-400' }}">
                            {{ $totalWeekHours >= 35 ? 'check_circle' : 'hourglass_empty' }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-text-secondary">Restant</p>
                        <p class="text-2xl font-bold text-white">{{ max(0, 35 - $totalWeekHours) }}h</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-blue-400">percent</span>
                    </div>
                    <div>
                        <p class="text-sm text-text-secondary">Completion</p>
                        <p class="text-2xl font-bold text-white">{{ min(100, round(($totalWeekHours / 35) * 100)) }}%</p>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <!-- Barre de progression -->
        <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-white">Progression hebdomadaire</span>
                <span class="text-sm text-text-secondary">{{ $totalWeekHours }}h / 35h</span>
            </div>
            <div class="w-full bg-surface-highlight rounded-full h-3">
                <div class="h-3 rounded-full transition-all duration-500 {{ $totalWeekHours >= 35 ? 'bg-green-500' : 'bg-primary' }}"
                     style="width: {{ min(100, ($totalWeekHours / 35) * 100) }}%"></div>
            </div>
            @if($totalWeekHours < 35)
                <p class="text-xs text-text-secondary mt-3">
                    <span class="material-symbols-outlined text-[14px] align-middle mr-1">info</span>
                    Pour saisir vos heures, utilisez la <a href="{{ route('hr.timesheets.daily') }}" class="text-primary hover:underline">vue journaliere</a>.
                </p>
            @else
                <p class="text-xs text-green-400 mt-3">
                    <span class="material-symbols-outlined text-[14px] align-middle mr-1">check_circle</span>
                    Objectif hebdomadaire atteint !
                </p>
            @endif
        </x-ui.card>
    @endif
</div>
