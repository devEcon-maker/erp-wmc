<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <p class="text-text-secondary text-sm">Total heures</p>
            <p class="text-2xl font-bold text-white">{{ number_format($timeStats['total_hours'], 1) }}h</p>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <p class="text-text-secondary text-sm">Heures facturables</p>
            <p class="text-2xl font-bold text-green-400">{{ number_format($timeStats['billable_hours'], 1) }}h</p>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <p class="text-text-secondary text-sm">Coût total</p>
            <p class="text-2xl font-bold text-primary">{{ number_format($timeStats['total_cost'], 0, ',', ' ') }} FCFA</p>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <p class="text-text-secondary text-sm">Approuvées</p>
            <p class="text-2xl font-bold text-blue-400">{{ number_format($timeStats['approved_hours'], 1) }}h</p>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <p class="text-text-secondary text-sm">En attente</p>
            <p class="text-2xl font-bold text-yellow-400">{{ number_format($timeStats['pending_hours'], 1) }}h</p>
        </div>
    </div>

    <!-- Time Entries Table -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-[#3a2e24] flex justify-between items-center">
            <h3 class="text-lg font-semibold text-white">Entrées de temps</h3>
            <a href="{{ route('productivity.time-tracker') }}" class="text-sm text-primary hover:text-primary/80 flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Ajouter du temps
            </a>
        </div>

        @if($project->timeEntries->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-surface-highlight">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Employé</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Tâche</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Description</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-text-secondary uppercase">Heures</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-text-secondary uppercase">Coût</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-text-secondary uppercase">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#3a2e24]">
                        @foreach($project->timeEntries->sortByDesc('date') as $entry)
                            <tr class="hover:bg-surface-highlight">
                                <td class="px-4 py-3 text-sm text-white">
                                    {{ $entry->date->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-xs font-medium text-primary">
                                            {{ substr($entry->employee->first_name ?? '', 0, 1) }}{{ substr($entry->employee->last_name ?? '', 0, 1) }}
                                        </div>
                                        <span class="text-sm text-white">{{ $entry->employee->full_name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-text-secondary">
                                    {{ $entry->task?->title ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-text-secondary max-w-xs truncate">
                                    {{ $entry->description ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-white text-right font-medium">
                                    {{ number_format($entry->hours, 1) }}h
                                </td>
                                <td class="px-4 py-3 text-sm text-primary text-right font-medium">
                                    {{ number_format($entry->cost, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($entry->approved === true)
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-green-500/20 text-green-400">
                                            <span class="material-symbols-outlined text-[14px] mr-1">check_circle</span>
                                            Approuvé
                                        </span>
                                    @elseif($entry->approved === false)
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-red-500/20 text-red-400">
                                            <span class="material-symbols-outlined text-[14px] mr-1">cancel</span>
                                            Refusé
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-yellow-500/20 text-yellow-400">
                                            <span class="material-symbols-outlined text-[14px] mr-1">pending</span>
                                            En attente
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-12 text-center">
                <span class="material-symbols-outlined text-5xl text-text-secondary mb-4">schedule</span>
                <h3 class="text-lg font-medium text-white mb-2">Aucune entrée de temps</h3>
                <p class="text-text-secondary mb-4">Commencez à suivre le temps passé sur ce projet.</p>
                <a href="{{ route('productivity.time-tracker') }}"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/80 inline-flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    Ajouter du temps
                </a>
            </div>
        @endif
    </div>
</div>
