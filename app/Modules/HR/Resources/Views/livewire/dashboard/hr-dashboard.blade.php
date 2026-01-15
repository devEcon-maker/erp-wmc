<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard RH</h1>
        <p class="text-gray-600 dark:text-gray-400">Vue d'ensemble des ressources humaines</p>
    </div>

    <!-- Statistiques principales -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Employés actifs</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $employeeStats['active'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Présents aujourd'hui</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $attendanceStats['present_today'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Congés en attente</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $leaveStats['pending_requests'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-purple-100 dark:bg-purple-900 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Postes ouverts</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $recruitmentStats['open_positions'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Alertes RH -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Alertes RH</h2>
            </div>
            <div class="p-4">
                @forelse($alerts as $alert)
                    <div class="flex items-start gap-3 p-3 rounded-lg mb-2
                        @if($alert->priority === 'critical') bg-red-50 dark:bg-red-900/20
                        @elseif($alert->priority === 'high') bg-orange-50 dark:bg-orange-900/20
                        @else bg-gray-50 dark:bg-gray-700
                        @endif">
                        <div class="flex-shrink-0">
                            @if($alert->priority === 'critical')
                                <span class="w-2 h-2 bg-red-500 rounded-full block mt-2"></span>
                            @elseif($alert->priority === 'high')
                                <span class="w-2 h-2 bg-orange-500 rounded-full block mt-2"></span>
                            @else
                                <span class="w-2 h-2 bg-gray-400 rounded-full block mt-2"></span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $alert->title }}</p>
                            @if($alert->employee)
                                <p class="text-sm text-gray-500">{{ $alert->employee->full_name }}</p>
                            @endif
                            <p class="text-xs text-gray-400">{{ $alert->alert_date->format('d/m/Y') }}</p>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="acknowledgeAlert({{ $alert->id }})"
                                    class="text-xs text-blue-600 hover:text-blue-800">
                                Acquitter
                            </button>
                            <button wire:click="resolveAlert({{ $alert->id }})"
                                    class="text-xs text-green-600 hover:text-green-800">
                                Résoudre
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 py-4">Aucune alerte en attente</p>
                @endforelse
            </div>
        </div>

        <!-- Statistiques présence -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Présences aujourd'hui</h2>
            </div>
            <div class="p-4 space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Présents</span>
                    <span class="text-lg font-bold text-green-600">{{ $attendanceStats['present_today'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">En retard</span>
                    <span class="text-lg font-bold text-yellow-600">{{ $attendanceStats['late_today'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Absents</span>
                    <span class="text-lg font-bold text-red-600">{{ $attendanceStats['absent_today'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">En congé</span>
                    <span class="text-lg font-bold text-blue-600">{{ $leaveStats['on_leave_today'] }}</span>
                </div>
                @if($attendanceStats['average_check_in'])
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Arrivée moyenne</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $attendanceStats['average_check_in'] }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Répartition par département -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Répartition par département</h2>
            </div>
            <div class="p-4">
                @foreach($employeeStats['by_department'] as $dept)
                    <div class="flex items-center justify-between py-2">
                        <span class="text-gray-600 dark:text-gray-400">{{ $dept['name'] }}</span>
                        <div class="flex items-center gap-2">
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div class="bg-primary-600 h-2 rounded-full"
                                     style="width: {{ $employeeStats['active'] > 0 ? ($dept['count'] / $employeeStats['active'] * 100) : 0 }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white w-8 text-right">{{ $dept['count'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Recrutement -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recrutement</h2>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $recruitmentStats['open_positions'] }}</p>
                        <p class="text-xs text-gray-500">Postes ouverts</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $recruitmentStats['total_applications'] }}</p>
                        <p class="text-xs text-gray-500">Candidatures</p>
                    </div>
                    <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <p class="text-2xl font-bold text-blue-600">{{ $recruitmentStats['new_applications'] }}</p>
                        <p class="text-xs text-gray-500">Nouvelles (7j)</p>
                    </div>
                    <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <p class="text-2xl font-bold text-green-600">{{ $recruitmentStats['interviews_scheduled'] }}</p>
                        <p class="text-xs text-gray-500">Entretiens</p>
                    </div>
                </div>
                <a href="{{ route('hr.recruitment.applications.index') }}"
                   class="block text-center text-sm text-primary-600 hover:text-primary-800">
                    Voir les candidatures →
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Paie -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Paie</h2>
            </div>
            <div class="p-4 space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Période en cours</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $payrollStats['current_period'] }}</span>
                </div>
                @if($payrollStats['current_period_status'])
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Statut</span>
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            @if($payrollStats['current_period_status'] === 'draft') bg-gray-100 text-gray-800
                            @elseif($payrollStats['current_period_status'] === 'validated') bg-blue-100 text-blue-800
                            @elseif($payrollStats['current_period_status'] === 'paid') bg-green-100 text-green-800
                            @endif">
                            {{ ucfirst($payrollStats['current_period_status']) }}
                        </span>
                    </div>
                @endif
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Dernière masse salariale</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ number_format($payrollStats['last_period_total'], 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Avances en attente</span>
                    <span class="font-medium text-yellow-600">{{ $payrollStats['pending_advances'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Prêts actifs</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $payrollStats['active_loans'] }}</span>
                </div>
                <a href="{{ route('hr.payroll.periods.index') }}"
                   class="block text-center text-sm text-primary-600 hover:text-primary-800 pt-2">
                    Gérer la paie →
                </a>
            </div>
        </div>

        <!-- Congés à venir -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Congés à venir (7 jours)</h2>
            </div>
            <div class="p-4">
                @forelse($leaveStats['upcoming_leaves'] as $leave)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $leave->employee->full_name }}</p>
                            <p class="text-xs text-gray-500">{{ $leave->start_date->format('d/m') }} - {{ $leave->end_date->format('d/m') }}</p>
                        </div>
                        <span class="text-sm text-gray-500">{{ $leave->days_count }} j</span>
                    </div>
                @empty
                    <p class="text-center text-gray-500 py-4">Aucun congé prévu</p>
                @endforelse
                <a href="{{ route('hr.leaves.requests') }}"
                   class="block text-center text-sm text-primary-600 hover:text-primary-800 pt-2">
                    Voir tous les congés →
                </a>
            </div>
        </div>
    </div>
</div>
