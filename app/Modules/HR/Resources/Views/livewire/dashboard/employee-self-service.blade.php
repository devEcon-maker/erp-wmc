<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Mon espace employé</h1>
        <p class="text-gray-600 dark:text-gray-400">Bienvenue, {{ auth()->user()->name }}</p>
    </div>

    @if(!$employee)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <svg class="mx-auto h-12 w-12 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-yellow-800">Compte non associé</h3>
            <p class="mt-1 text-yellow-700">Votre compte utilisateur n'est pas encore associé à un dossier employé. Veuillez contacter le service RH.</p>
        </div>
    @else
        <!-- Pointage rapide -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pointage du jour</h2>
                    <p class="text-gray-500">{{ now()->format('l d F Y') }}</p>
                </div>

                <div class="flex items-center gap-4">
                    @if($todayAttendance)
                        <div class="text-center">
                            <p class="text-xs text-gray-500">Entrée</p>
                            <p class="text-lg font-bold text-green-600">
                                {{ $todayAttendance->check_in ? \Carbon\Carbon::parse($todayAttendance->check_in)->format('H:i') : '--:--' }}
                            </p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-500">Sortie</p>
                            <p class="text-lg font-bold text-blue-600">
                                {{ $todayAttendance->check_out ? \Carbon\Carbon::parse($todayAttendance->check_out)->format('H:i') : '--:--' }}
                            </p>
                        </div>
                    @endif

                    @if(!$todayAttendance || !$todayAttendance->check_in)
                        <button wire:click="checkIn"
                                class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Pointer mon entrée
                        </button>
                    @elseif(!$todayAttendance->check_out)
                        <button wire:click="checkOut"
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Pointer ma sortie
                        </button>
                    @else
                        <span class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg">
                            Journée complète
                        </span>
                    @endif
                </div>
            </div>

            @if($todayAttendance && $todayAttendance->is_late)
                <div class="mt-4 p-3 bg-yellow-50 text-yellow-800 rounded-lg text-sm">
                    Vous êtes arrivé en retard aujourd'hui ({{ $todayAttendance->late_minutes }} minutes).
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Soldes de congés -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Mes congés</h2>
                </div>
                <div class="p-4 space-y-3">
                    @forelse($leaveBalances as $balance)
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">{{ $balance->leaveType->name }}</span>
                            <span class="font-bold text-gray-900 dark:text-white">
                                {{ $balance->remaining_days }} / {{ $balance->entitled_days }} j
                            </span>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">Aucun solde configuré</p>
                    @endforelse
                    <a href="{{ route('hr.leaves.requests.create') }}"
                       class="block mt-4 text-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm">
                        Demander un congé
                    </a>
                </div>
            </div>

            <!-- Statistiques présence -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Ma présence (ce mois)</h2>
                </div>
                <div class="p-4 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Jours présent</span>
                        <span class="font-bold text-green-600">{{ $attendanceStats['present_days'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Retards</span>
                        <span class="font-bold text-yellow-600">{{ $attendanceStats['late_days'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Heures travaillées</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ number_format($attendanceStats['total_hours'], 1) }}h</span>
                    </div>
                    @if($attendanceStats['overtime_hours'] > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Heures sup.</span>
                            <span class="font-bold text-blue-600">+{{ number_format($attendanceStats['overtime_hours'], 1) }}h</span>
                        </div>
                    @endif
                    <a href="{{ route('hr.attendance.my') }}"
                       class="block mt-4 text-center text-sm text-primary-600 hover:text-primary-800">
                        Voir mon historique →
                    </a>
                </div>
            </div>

            <!-- Dernier bulletin -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Dernier bulletin</h2>
                </div>
                <div class="p-4">
                    @if($lastPayslip)
                        <p class="text-gray-500 text-sm mb-2">{{ $lastPayslip->payrollPeriod->name }}</p>
                        <p class="text-3xl font-bold text-green-600">{{ number_format($lastPayslip->net_salary, 0, ',', ' ') }}</p>
                        <p class="text-xs text-gray-400">FCFA</p>
                        <a href="{{ route('hr.payroll.payslips.show', $lastPayslip) }}"
                           class="block mt-4 text-center text-sm text-primary-600 hover:text-primary-800">
                            Voir le bulletin →
                        </a>
                    @else
                        <p class="text-gray-500 text-sm">Aucun bulletin disponible</p>
                    @endif
                    <a href="{{ route('hr.payroll.my-payslips') }}"
                       class="block mt-4 text-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm dark:border-gray-600 dark:text-gray-300">
                        Tous mes bulletins
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Évaluations en attente -->
            @if($pendingEvaluations->count() > 0 || $teamEvaluations->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Évaluations</h2>
                    </div>
                    <div class="p-4 space-y-3">
                        @foreach($pendingEvaluations as $eval)
                            <div class="flex items-center justify-between p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $eval->evaluationPeriod->name }}</p>
                                    <p class="text-xs text-gray-500">Auto-évaluation à compléter</p>
                                </div>
                                <a href="{{ route('hr.evaluations.form', $eval) }}"
                                   class="px-3 py-1 bg-primary-600 text-white rounded text-sm hover:bg-primary-700">
                                    Compléter
                                </a>
                            </div>
                        @endforeach
                        @foreach($teamEvaluations as $eval)
                            <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $eval->employee->full_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $eval->evaluationPeriod->name }}</p>
                                </div>
                                <a href="{{ route('hr.evaluations.form', $eval) }}"
                                   class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                                    Évaluer
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Objectifs -->
            @if($activeObjectives->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Mes objectifs</h2>
                    </div>
                    <div class="p-4 space-y-3">
                        @foreach($activeObjectives as $objective)
                            <div class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div class="flex items-start justify-between mb-2">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $objective->title }}</p>
                                    <span class="text-sm font-bold
                                        @if($objective->progress_percentage >= 75) text-green-600
                                        @elseif($objective->progress_percentage >= 50) text-blue-600
                                        @else text-gray-500
                                        @endif">
                                        {{ $objective->progress_percentage }}%
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                    <div class="h-2 rounded-full
                                        @if($objective->progress_percentage >= 75) bg-green-600
                                        @elseif($objective->progress_percentage >= 50) bg-blue-600
                                        @else bg-gray-400
                                        @endif"
                                         style="width: {{ $objective->progress_percentage }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500">
                                    Échéance: {{ $objective->due_date->format('d/m/Y') }}
                                    @if($objective->due_date->isPast())
                                        <span class="text-red-500">(En retard)</span>
                                    @endif
                                </p>
                            </div>
                        @endforeach
                        <a href="{{ route('hr.evaluations.my-evaluations') }}"
                           class="block text-center text-sm text-primary-600 hover:text-primary-800">
                            Voir tous mes objectifs →
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Historique récent des présences -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Mes 7 derniers jours</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Entrée</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Sortie</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Heures</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recentAttendances as $attendance)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">
                                    {{ $attendance->date->format('D d/m') }}
                                </td>
                                <td class="px-4 py-2 text-sm text-center text-gray-500">
                                    {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-' }}
                                </td>
                                <td class="px-4 py-2 text-sm text-center text-gray-500">
                                    {{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '-' }}
                                </td>
                                <td class="px-4 py-2 text-sm text-center text-gray-900 dark:text-white">
                                    {{ $attendance->worked_hours ? number_format($attendance->worked_hours, 1) . 'h' : '-' }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    @if($attendance->is_late)
                                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Retard</span>
                                    @elseif($attendance->check_in)
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Présent</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-gray-500">
                                    Aucun historique disponible
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
