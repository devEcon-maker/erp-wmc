<div>
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $application->full_name }}</h1>
            <p class="text-gray-500">
                Candidature pour {{ $application->jobPosition->title }}
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="px-3 py-1 rounded-full bg-{{ $application->status_color }}-100 text-{{ $application->status_color }}-800 font-medium">
                {{ $application->status_label }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations principales -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Coordonnées -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Coordonnées</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-500">Email</label>
                        <p class="font-medium">
                            <a href="mailto:{{ $application->email }}" class="text-primary-600 hover:underline">
                                {{ $application->email }}
                            </a>
                        </p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Téléphone</label>
                        <p class="font-medium">
                            @if($application->phone)
                                <a href="tel:{{ $application->phone }}" class="text-primary-600 hover:underline">
                                    {{ $application->phone }}
                                </a>
                            @else
                                <span class="text-gray-400">Non renseigné</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- CV et lettre -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Documents</h2>

                @if($application->resume_path)
                    <div class="mb-4">
                        <label class="text-sm text-gray-500">CV</label>
                        <div class="mt-1">
                            <a href="{{ Storage::url($application->resume_path) }}" target="_blank"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Télécharger le CV
                            </a>
                        </div>
                    </div>
                @endif

                @if($application->cover_letter)
                    <div>
                        <label class="text-sm text-gray-500">Lettre de motivation</label>
                        <div class="mt-2 p-4 bg-gray-50 rounded-lg text-gray-700">
                            {!! nl2br(e($application->cover_letter)) !!}
                        </div>
                    </div>
                @else
                    <p class="text-gray-400">Aucune lettre de motivation</p>
                @endif
            </div>

            <!-- Notes internes -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Notes internes</h2>
                <textarea wire:model.blur="notes" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm"
                    placeholder="Ajoutez des notes sur ce candidat..."></textarea>
                <p class="text-xs text-gray-500 mt-1">Les notes sont sauvegardées automatiquement</p>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Infos candidature -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Candidature</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm text-gray-500">Poste</dt>
                        <dd>
                            <a href="{{ route('hr.recruitment.positions.show', $application->jobPosition) }}"
                                class="text-primary-600 hover:underline font-medium">
                                {{ $application->jobPosition->title }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Département</dt>
                        <dd class="font-medium">{{ $application->jobPosition->department->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Date de candidature</dt>
                        <dd class="font-medium">{{ $application->applied_at->format('d/m/Y à H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Notation -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Évaluation</h3>
                <div class="flex space-x-1">
                    @for($i = 1; $i <= 5; $i++)
                        <button wire:click="setRating({{ $i }})"
                            class="text-2xl {{ $application->rating >= $i ? 'text-yellow-500' : 'text-gray-300' }} hover:text-yellow-400 transition">
                            ★
                        </button>
                    @endfor
                </div>
                @if($application->rating)
                    <button wire:click="setRating(0)" class="text-xs text-gray-500 hover:underline mt-2">
                        Effacer
                    </button>
                @endif
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Changer le statut</h3>
                <div class="space-y-2">
                    @if($application->status === 'new')
                        <button wire:click="updateStatus('reviewing')" class="w-full px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                            Passer en revue
                        </button>
                    @endif

                    @if(in_array($application->status, ['new', 'reviewing']))
                        <button wire:click="updateStatus('interview')" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            Planifier un entretien
                        </button>
                    @endif

                    @if($application->status === 'interview')
                        <button wire:click="updateStatus('offer')" class="w-full px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                            Faire une offre
                        </button>
                    @endif

                    @if($application->status === 'offer')
                        <button wire:click="hire" class="w-full px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                            Embaucher
                        </button>
                    @endif

                    @if(!in_array($application->status, ['hired', 'rejected']))
                        <button wire:click="updateStatus('rejected')" class="w-full px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50">
                            Rejeter
                        </button>
                    @endif
                </div>
            </div>

            <!-- Lien retour -->
            <a href="{{ route('hr.recruitment.applications.index') }}" class="block w-full px-4 py-2 text-center border border-gray-300 rounded-lg hover:bg-gray-50">
                Retour aux candidatures
            </a>

            <!-- Bouton supprimer -->
            <button wire:click="confirmDelete" class="block w-full px-4 py-2 text-center border border-red-300 text-red-600 rounded-lg hover:bg-red-50 mt-2">
                Supprimer la candidature
            </button>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-xl">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Confirmer la suppression</h3>
                        <p class="text-gray-500 text-sm">Cette action est irréversible</p>
                    </div>
                </div>
                <p class="text-gray-600 mb-6">
                    Êtes-vous sûr de vouloir supprimer la candidature de <strong>{{ $application->full_name }}</strong> ?
                </p>
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Annuler
                    </button>
                    <button wire:click="deleteApplication" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
