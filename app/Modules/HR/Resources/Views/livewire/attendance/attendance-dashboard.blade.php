<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Tableau de Bord Presences</h1>
        <p class="text-text-secondary mt-1">Vue d'ensemble des presences du jour</p>
    </div>

    <!-- Filtres -->
    <div class="flex flex-wrap items-center gap-4 mb-6">
        <input type="date" wire:model.live="selectedDate"
            class="bg-surface-dark border border-white/10 rounded-lg text-white px-3 py-2">
        <select wire:model.live="selectedDepartment"
            class="bg-surface-dark border border-white/10 rounded-lg text-white px-3 py-2">
            <option value="">Tous les departements</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Stats du jour -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-surface-dark border border-white/10 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <span class="text-text-secondary text-sm">Total</span>
                <span class="material-symbols-outlined text-blue-400">groups</span>
            </div>
            <p class="text-2xl font-bold text-white mt-2">{{ $todayStats['total'] ?? 0 }}</p>
        </div>
        <div class="bg-surface-dark border border-white/10 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <span class="text-text-secondary text-sm">Presents</span>
                <span class="material-symbols-outlined text-green-400">check_circle</span>
            </div>
            <p class="text-2xl font-bold text-green-400 mt-2">{{ $todayStats['present'] ?? 0 }}</p>
        </div>
        <div class="bg-surface-dark border border-white/10 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <span class="text-text-secondary text-sm">Absents</span>
                <span class="material-symbols-outlined text-red-400">cancel</span>
            </div>
            <p class="text-2xl font-bold text-red-400 mt-2">{{ $todayStats['absent'] ?? 0 }}</p>
        </div>
        <div class="bg-surface-dark border border-white/10 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <span class="text-text-secondary text-sm">Retards</span>
                <span class="material-symbols-outlined text-amber-400">schedule</span>
            </div>
            <p class="text-2xl font-bold text-amber-400 mt-2">{{ $todayStats['late'] ?? 0 }}</p>
        </div>
        <div class="bg-surface-dark border border-white/10 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <span class="text-text-secondary text-sm">En conge</span>
                <span class="material-symbols-outlined text-cyan-400">beach_access</span>
            </div>
            <p class="text-2xl font-bold text-cyan-400 mt-2">{{ $todayStats['on_leave'] ?? 0 }}</p>
        </div>
        <div class="bg-surface-dark border border-white/10 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <span class="text-text-secondary text-sm">Teletravail</span>
                <span class="material-symbols-outlined text-purple-400">home_work</span>
            </div>
            <p class="text-2xl font-bold text-purple-400 mt-2">{{ $todayStats['remote'] ?? 0 }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Employes pointes -->
        <div class="bg-surface-dark border border-white/10 rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Employes pointes</h3>
                <span class="text-text-secondary text-sm">{{ $attendances->count() }} employes</span>
            </div>
            <div class="max-h-96 overflow-y-auto">
                @forelse($attendances as $attendance)
                    <div class="px-6 py-3 border-b border-white/5 hover:bg-white/5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-primary text-sm font-medium">
                                {{ substr($attendance->employee->first_name, 0, 1) }}{{ substr($attendance->employee->last_name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-white text-sm font-medium">{{ $attendance->employee->full_name }}</p>
                                <p class="text-text-secondary text-xs">{{ $attendance->employee->department?->name }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $attendance->status_color }}-500/20 text-{{ $attendance->status_color }}-400">
                                {{ $attendance->status_label }}
                            </span>
                            <p class="text-text-secondary text-xs mt-1">
                                {{ $attendance->formatted_check_in ?? '-' }} - {{ $attendance->formatted_check_out ?? 'En cours' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-text-secondary">
                        Aucun pointage enregistre
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Employes non pointes -->
        <div class="bg-surface-dark border border-white/10 rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Non pointes</h3>
                <span class="text-red-400 text-sm">{{ $missingEmployees->count() }} employes</span>
            </div>
            <div class="max-h-96 overflow-y-auto">
                @forelse($missingEmployees as $employee)
                    <div class="px-6 py-3 border-b border-white/5 hover:bg-white/5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center text-red-400 text-sm font-medium">
                                {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-white text-sm font-medium">{{ $employee->full_name }}</p>
                                <p class="text-text-secondary text-xs">{{ $employee->department?->name }}</p>
                            </div>
                        </div>
                        <span class="text-red-400 text-xs">Absent</span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-green-400">
                        <span class="material-symbols-outlined text-4xl">check_circle</span>
                        <p class="mt-2">Tous les employes ont pointe</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Stats semaine -->
    <div class="mt-6 bg-surface-dark border border-white/10 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Statistiques de la semaine</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
                <p class="text-3xl font-bold text-white">{{ $weeklyStats['total_worked_hours'] ?? 0 }}h</p>
                <p class="text-text-secondary text-sm">Heures travaillees</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-cyan-400">{{ $weeklyStats['total_overtime'] ?? 0 }}h</p>
                <p class="text-text-secondary text-sm">Heures supplementaires</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-amber-400">{{ $weeklyStats['late_count'] ?? 0 }}</p>
                <p class="text-text-secondary text-sm">Total retards</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-red-400">{{ $weeklyStats['absent_count'] ?? 0 }}</p>
                <p class="text-text-secondary text-sm">Total absences</p>
            </div>
        </div>
    </div>
</div>
