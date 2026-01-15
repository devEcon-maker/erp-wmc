<div>
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
            <a href="{{ route('hr.evaluations.my-evaluations') }}" class="hover:text-primary-600">Mes évaluations</a>
            <span>/</span>
            <span>{{ $evaluation->evaluationPeriod->name }}</span>
        </div>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Évaluation de {{ $evaluation->employee->full_name }}
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ $evaluation->evaluationPeriod->name }}
                    @if($isSelfEvaluation)
                        - <span class="text-primary-600 font-medium">Auto-évaluation</span>
                    @else
                        - <span class="text-blue-600 font-medium">Évaluation manager</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <form wire:submit="submit">
        <!-- Informations employé -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations de l'employé</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Nom</span>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $evaluation->employee->full_name }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Poste</span>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $evaluation->employee->jobPosition?->title ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Département</span>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $evaluation->employee->department?->name ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Ancienneté</span>
                    <p class="font-medium text-gray-900 dark:text-white">
                        @if($evaluation->employee->hire_date)
                            {{ $evaluation->employee->hire_date->diffForHumans(null, true) }}
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Critères par catégorie -->
        @foreach($criteria as $category => $categoryCriteria)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ $categories[$category] ?? ucfirst($category) }}
                </h3>

                <div class="space-y-6">
                    @foreach($categoryCriteria as $criterion)
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-6 last:border-0 last:pb-0">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-white">
                                        {{ $criterion->name }}
                                        @if($criterion->is_required)
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $criterion->description }}</p>
                                </div>
                                <span class="text-xs text-gray-400">Poids: {{ $criterion->weight }}</span>
                            </div>

                            <!-- Notation -->
                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Note (1-5)</label>
                                <div class="flex gap-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button type="button"
                                                wire:click="$set('scores.{{ $criterion->id }}', {{ $i }})"
                                                class="w-12 h-12 rounded-lg border-2 transition
                                                    @if(($scores[$criterion->id] ?? 0) === $i)
                                                        @if($i >= 4) border-green-500 bg-green-100 text-green-700
                                                        @elseif($i >= 3) border-blue-500 bg-blue-100 text-blue-700
                                                        @elseif($i >= 2) border-yellow-500 bg-yellow-100 text-yellow-700
                                                        @else border-red-500 bg-red-100 text-red-700
                                                        @endif
                                                    @else
                                                        border-gray-300 hover:border-gray-400 dark:border-gray-600
                                                    @endif
                                                ">
                                            {{ $i }}
                                        </button>
                                    @endfor
                                </div>
                                <div class="flex justify-between text-xs text-gray-500 mt-1">
                                    <span>Insuffisant</span>
                                    <span>Excellent</span>
                                </div>
                            </div>

                            <!-- Commentaire -->
                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Commentaire</label>
                                <textarea wire:model="comments.{{ $criterion->id }}" rows="2"
                                          placeholder="Commentaire optionnel..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm"></textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <!-- Commentaires généraux -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Commentaires généraux</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ $isSelfEvaluation ? 'Auto-évaluation générale' : 'Évaluation générale' }}
                    </label>
                    <textarea wire:model="generalComments" rows="4"
                              placeholder="Commentaires généraux sur la période..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                </div>

                @if(!$isSelfEvaluation)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Points forts</label>
                        <textarea wire:model="strengths" rows="3"
                                  placeholder="Les points forts de l'employé..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Axes d'amélioration</label>
                        <textarea wire:model="improvements" rows="3"
                                  placeholder="Les points à améliorer..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Recommandations</label>
                        <textarea wire:model="recommendations" rows="3"
                                  placeholder="Recommandations (formation, promotion, etc.)..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('hr.evaluations.my-evaluations') }}"
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                Annuler
            </a>
            <button type="button" wire:click="save"
                    class="px-6 py-2 border border-primary-600 text-primary-600 rounded-lg hover:bg-primary-50 dark:hover:bg-primary-900/20">
                Sauvegarder
            </button>
            <button type="submit"
                    class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                Soumettre l'évaluation
            </button>
        </div>
    </form>
</div>
