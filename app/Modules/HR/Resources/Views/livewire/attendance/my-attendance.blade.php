<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Mon Pointage</h1>
        <p class="text-text-secondary mt-1">Consultez et gerez vos pointages</p>
    </div>

    @if(!$employee)
        <div class="bg-amber-500/10 border border-amber-500/30 rounded-lg p-6">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-amber-400">warning</span>
                <p class="text-amber-400">Votre profil employe n'est pas configure. Contactez les RH.</p>
            </div>
        </div>
    @else
        <!-- Pointage du jour -->
        <div class="bg-surface-dark border border-white/10 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-white mb-4">Aujourd'hui - {{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Statut actuel -->
                <div class="text-center">
                    @if($todayAttendance)
                        <div class="mb-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($todayAttendance->status === 'present') bg-green-500/20 text-green-400
                                @elseif($todayAttendance->status === 'late') bg-amber-500/20 text-amber-400
                                @else bg-gray-500/20 text-gray-400 @endif">
                                {{ $todayAttendance->status_label }}
                            </span>
                        </div>
                        <div class="text-3xl font-bold text-white mb-2">
                            {{ $todayAttendance->formatted_worked_hours ?? '0h00' }}
                        </div>
                        <p class="text-text-secondary text-sm">Heures travaillees</p>
                    @else
                        <div class="text-amber-400 mb-2">
                            <span class="material-symbols-outlined text-4xl">schedule</span>
                        </div>
                        <p class="text-text-secondary">Pas encore pointe</p>
                    @endif
                </div>

                <!-- Heure d'entree -->
                <div class="text-center border-x border-white/10 px-4">
                    <p class="text-text-secondary text-sm mb-2">Entree</p>
                    @if($todayAttendance && $todayAttendance->check_in)
                        <p class="text-2xl font-bold text-white">{{ $todayAttendance->formatted_check_in }}</p>
                        @if($todayAttendance->is_late)
                            <p class="text-amber-400 text-sm mt-1">
                                Retard de {{ $todayAttendance->late_minutes }} min
                            </p>
                        @endif
                    @else
                        <button wire:click="checkIn"
                            class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold flex items-center gap-2 mx-auto">
                            <span class="material-symbols-outlined">login</span>
                            Pointer l'entree
                        </button>
                    @endif
                </div>

                <!-- Heure de sortie -->
                <div class="text-center">
                    <p class="text-text-secondary text-sm mb-2">Sortie</p>
                    @if($todayAttendance && $todayAttendance->check_out)
                        <p class="text-2xl font-bold text-white">{{ $todayAttendance->formatted_check_out }}</p>
                    @elseif($todayAttendance && $todayAttendance->check_in)
                        <button wire:click="checkOut"
                            class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold flex items-center gap-2 mx-auto">
                            <span class="material-symbols-outlined">logout</span>
                            Pointer la sortie
                        </button>
                    @else
                        <p class="text-gray-500">-</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistiques du mois -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-surface-dark border border-white/10 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-green-400">{{ $monthStats['present_days'] ?? 0 }}</p>
                <p class="text-text-secondary text-sm">Jours presents</p>
            </div>
            <div class="bg-surface-dark border border-white/10 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-red-400">{{ $monthStats['absent_days'] ?? 0 }}</p>
                <p class="text-text-secondary text-sm">Jours absents</p>
            </div>
            <div class="bg-surface-dark border border-white/10 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-amber-400">{{ $monthStats['late_count'] ?? 0 }}</p>
                <p class="text-text-secondary text-sm">Retards</p>
            </div>
            <div class="bg-surface-dark border border-white/10 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-cyan-400">{{ number_format($monthStats['attendance_rate'] ?? 0, 1) }}%</p>
                <p class="text-text-secondary text-sm">Taux presence</p>
            </div>
        </div>

        <!-- Selection du mois -->
        <div class="flex items-center gap-4 mb-4">
            <select wire:model.live="selectedMonth" class="bg-surface-dark border border-white/10 rounded-lg text-white px-3 py-2">
                @foreach(range(1, 12) as $month)
                    <option value="{{ $month }}">{{ \Carbon\Carbon::create(null, $month)->locale('fr')->monthName }}</option>
                @endforeach
            </select>
            <select wire:model.live="selectedYear" class="bg-surface-dark border border-white/10 rounded-lg text-white px-3 py-2">
                @foreach(range(now()->year - 1, now()->year + 1) as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>

        <!-- Historique du mois -->
        <div class="bg-surface-dark border border-white/10 rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10">
                <h3 class="text-lg font-semibold text-white">Historique du mois</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-surface-darker">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Entree</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Sortie</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Heures</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">HS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($monthAttendances as $attendance)
                            <tr class="hover:bg-white/5">
                                <td class="px-6 py-4 text-white">
                                    {{ $attendance->date->locale('fr')->isoFormat('ddd D MMM') }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $attendance->status_color }}-500/20 text-{{ $attendance->status_color }}-400">
                                        {{ $attendance->status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-white">
                                    {{ $attendance->formatted_check_in ?? '-' }}
                                    @if($attendance->is_late)
                                        <span class="text-amber-400 text-xs ml-1">(+{{ $attendance->late_minutes }}min)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-white">{{ $attendance->formatted_check_out ?? '-' }}</td>
                                <td class="px-6 py-4 text-white">{{ $attendance->formatted_worked_hours ?? '-' }}</td>
                                <td class="px-6 py-4 text-cyan-400">
                                    {{ $attendance->overtime_hours > 0 ? number_format($attendance->overtime_hours, 1) . 'h' : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-text-secondary">
                                    Aucun pointage ce mois
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
