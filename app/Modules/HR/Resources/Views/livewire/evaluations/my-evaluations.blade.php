<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Mes évaluations</h1>
        <p class="text-gray-600 dark:text-gray-400">Gérez vos évaluations et objectifs</p>
    </div>

    @if(!$this->employee)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-yellow-800">
            <p>Votre compte n'est pas associé à un dossier employé. Contactez les RH.</p>
        </div>
    @else
        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
            <nav class="flex space-x-8">
                <button wire:click="$set('activeTab', 'evaluations')"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition
                            @if($activeTab === 'evaluations')
                                border-primary-500 text-primary-600
                            @else
                                border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300
                            @endif">
                    Évaluations en cours
                    @if($pendingEvaluations->count() > 0)
                        <span class="ml-2 bg-primary-100 text-primary-600 px-2 py-0.5 rounded-full text-xs">{{ $pendingEvaluations->count() }}</span>
                    @endif
                </button>
                <button wire:click="$set('activeTab', 'team')"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition
                            @if($activeTab === 'team')
                                border-primary-500 text-primary-600
                            @else
                                border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300
                            @endif">
                    Équipe à évaluer
                    @if($teamEvaluations->count() > 0)
                        <span class="ml-2 bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full text-xs">{{ $teamEvaluations->count() }}</span>
                    @endif
                </button>
                <button wire:click="$set('activeTab', 'objectives')"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition
                            @if($activeTab === 'objectives')
                                border-primary-500 text-primary-600
                            @else
                                border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300
                            @endif">
                    Mes objectifs
                    @if($objectives->count() > 0)
                        <span class="ml-2 bg-green-100 text-green-600 px-2 py-0.5 rounded-full text-xs">{{ $objectives->count() }}</span>
                    @endif
                </button>
                <button wire:click="$set('activeTab', 'history')"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition
                            @if($activeTab === 'history')
                                border-primary-500 text-primary-600
                            @else
                                border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300
                            @endif">
                    Historique
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        @if($activeTab === 'evaluations')
            <!-- Évaluations en cours -->
            <div class="space-y-4">
                @forelse($pendingEvaluations as $evaluation)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $evaluation->evaluationPeriod->name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Date limite: {{ $evaluation->evaluationPeriod->end_date->format('d/m/Y') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($evaluation->status === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-blue-100 text-blue-800
                                    @endif">
                                    {{ $evaluation->status === 'pending' ? 'À démarrer' : 'En cours' }}
                                </span>
                                <a href="{{ route('hr.evaluations.form', $evaluation) }}"
                                   class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm">
                                    Compléter
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucune évaluation en attente</h3>
                        <p class="mt-1 text-sm text-gray-500">Vous n'avez pas d'évaluation à compléter.</p>
                    </div>
                @endforelse
            </div>
        @elseif($activeTab === 'team')
            <!-- Équipe à évaluer -->
            <div class="space-y-4">
                @forelse($teamEvaluations as $evaluation)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                                    <span class="text-primary-600 dark:text-primary-400 font-medium">
                                        {{ substr($evaluation->employee->first_name, 0, 1) }}{{ substr($evaluation->employee->last_name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $evaluation->employee->full_name }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $evaluation->evaluationPeriod->name }}
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('hr.evaluations.form', $evaluation) }}"
                               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                                Évaluer
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucune évaluation d'équipe</h3>
                        <p class="mt-1 text-sm text-gray-500">Vous n'avez pas de membre d'équipe à évaluer.</p>
                    </div>
                @endforelse
            </div>
        @elseif($activeTab === 'objectives')
            <!-- Objectifs -->
            <div class="space-y-4">
                @forelse($objectives as $objective)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $objective->title }}</h3>
                                    <span class="px-2 py-0.5 text-xs rounded-full
                                        @if($objective->priority === 'high') bg-red-100 text-red-800
                                        @elseif($objective->priority === 'medium') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($objective->priority) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $objective->description }}</p>
                                <p class="text-xs text-gray-400 mt-2">
                                    Échéance: {{ $objective->due_date->format('d/m/Y') }}
                                    @if($objective->due_date->isPast())
                                        <span class="text-red-500">(En retard)</span>
                                    @endif
                                </p>
                            </div>
                            <div class="text-right ml-4">
                                <div class="text-2xl font-bold
                                    @if($objective->progress_percentage >= 75) text-green-600
                                    @elseif($objective->progress_percentage >= 50) text-blue-600
                                    @elseif($objective->progress_percentage >= 25) text-yellow-600
                                    @else text-gray-400
                                    @endif">
                                    {{ $objective->progress_percentage }}%
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
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
                    </div>
                @empty
                    <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucun objectif</h3>
                        <p class="mt-1 text-sm text-gray-500">Vous n'avez pas d'objectif actif.</p>
                    </div>
                @endforelse
            </div>
        @else
            <!-- Historique -->
            <div class="space-y-4">
                @forelse($evaluationHistory as $evaluation)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $evaluation->evaluationPeriod->name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Complétée le {{ $evaluation->completed_at?->format('d/m/Y') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-4">
                                @if($evaluation->final_score)
                                    <div class="text-center">
                                        <div class="text-2xl font-bold
                                            @if($evaluation->final_score >= 4) text-green-600
                                            @elseif($evaluation->final_score >= 3) text-blue-600
                                            @elseif($evaluation->final_score >= 2) text-yellow-600
                                            @else text-red-600
                                            @endif">
                                            {{ number_format($evaluation->final_score, 1) }}/5
                                        </div>
                                        <div class="text-xs text-gray-500">Score final</div>
                                    </div>
                                @endif
                                <a href="{{ route('hr.evaluations.form', $evaluation) }}"
                                   class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm dark:border-gray-600 dark:text-gray-300">
                                    Voir détails
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucun historique</h3>
                        <p class="mt-1 text-sm text-gray-500">Vous n'avez pas encore d'évaluation complétée.</p>
                    </div>
                @endforelse
            </div>
        @endif
    @endif
</div>
