<div>
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
            <a href="{{ route('hr.evaluations.periods.index') }}" class="hover:text-primary-600">Campagnes</a>
            <span>/</span>
            <span>{{ $evaluationPeriod->name }}</span>
        </div>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $evaluationPeriod->name }}</h1>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ $evaluationPeriod->start_date->format('d/m/Y') }} - {{ $evaluationPeriod->end_date->format('d/m/Y') }}
                    | Template: {{ $evaluationPeriod->template?->name }}
                </p>
            </div>
            <span class="px-3 py-1 text-sm font-medium rounded-full
                @if($evaluationPeriod->status === 'draft') bg-gray-100 text-gray-800
                @elseif($evaluationPeriod->status === 'active') bg-green-100 text-green-800
                @elseif($evaluationPeriod->status === 'closed') bg-blue-100 text-blue-800
                @endif">
                {{ \App\Modules\HR\Models\EvaluationPeriod::STATUSES[$evaluationPeriod->status] ?? $evaluationPeriod->status }}
            </span>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">Total</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">En attente</div>
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] + $stats['self_evaluation'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">Manager</div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['manager_evaluation'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">Complétées</div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">Taux</div>
            <div class="text-2xl font-bold text-primary-600">{{ $stats['completion_rate'] }}%</div>
        </div>
    </div>

    <!-- Barre de progression -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700 mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progression globale</span>
            <span class="text-sm text-gray-500">{{ $stats['completed'] }} / {{ $stats['total'] }} complétées</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3 dark:bg-gray-700">
            <div class="bg-green-600 h-3 rounded-full" style="width: {{ $stats['completion_rate'] }}%"></div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Rechercher un employé..."
                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">

            <select wire:model.live="departmentFilter"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">Tous les départements</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="statusFilter"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">Tous les statuts</option>
                <option value="pending">En attente</option>
                <option value="self_evaluation">Auto-évaluation</option>
                <option value="manager_evaluation">Évaluation manager</option>
                <option value="completed">Complétée</option>
            </select>
        </div>
    </div>

    <!-- Liste des évaluations -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Employé</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Département</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Évaluateur</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Score</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($evaluations as $evaluation)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                                            <span class="text-primary-600 dark:text-primary-400 font-medium text-sm">
                                                {{ substr($evaluation->employee->first_name, 0, 1) }}{{ substr($evaluation->employee->last_name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $evaluation->employee->full_name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $evaluation->employee->jobPosition?->title ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $evaluation->employee->department?->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $evaluation->evaluator?->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($evaluation->status === 'pending') bg-gray-100 text-gray-800
                                    @elseif($evaluation->status === 'self_evaluation') bg-yellow-100 text-yellow-800
                                    @elseif($evaluation->status === 'manager_evaluation') bg-blue-100 text-blue-800
                                    @elseif($evaluation->status === 'completed') bg-green-100 text-green-800
                                    @endif">
                                    @switch($evaluation->status)
                                        @case('pending') En attente @break
                                        @case('self_evaluation') Auto-éval @break
                                        @case('manager_evaluation') Manager @break
                                        @case('completed') Complétée @break
                                        @default {{ $evaluation->status }}
                                    @endswitch
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($evaluation->final_score)
                                    <span class="text-lg font-bold
                                        @if($evaluation->final_score >= 4) text-green-600
                                        @elseif($evaluation->final_score >= 3) text-blue-600
                                        @elseif($evaluation->final_score >= 2) text-yellow-600
                                        @else text-red-600
                                        @endif">
                                        {{ number_format($evaluation->final_score, 1) }}/5
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('hr.evaluations.form', $evaluation) }}"
                                       class="text-primary-600 hover:text-primary-800">
                                        @if($evaluation->status === 'completed') Voir @else Évaluer @endif
                                    </a>
                                    @if($evaluation->status !== 'completed')
                                        <button wire:click="sendReminder({{ $evaluation->id }})"
                                                class="text-blue-600 hover:text-blue-800">
                                            Rappel
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                Aucune évaluation pour cette période.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $evaluations->links() }}
    </div>
</div>
