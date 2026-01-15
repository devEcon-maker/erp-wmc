<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-xl bg-primary/20 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-primary text-2xl">work</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $jobPosition->title }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-1">
                    <span class="inline-flex items-center gap-1 text-text-secondary text-sm">
                        <span class="material-symbols-outlined text-[16px]">business</span>
                        {{ $jobPosition->department->name }}
                    </span>
                    @if($jobPosition->location)
                        <span class="text-text-muted">•</span>
                        <span class="inline-flex items-center gap-1 text-text-secondary text-sm">
                            <span class="material-symbols-outlined text-[16px]">location_on</span>
                            {{ $jobPosition->location }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @php
                $statusColors = [
                    'draft' => 'bg-gray-500/20 text-gray-400 border-gray-500/30',
                    'published' => 'bg-green-500/20 text-green-400 border-green-500/30',
                    'closed' => 'bg-red-500/20 text-red-400 border-red-500/30',
                ];
                $statusIcons = [
                    'draft' => 'edit_note',
                    'published' => 'public',
                    'closed' => 'lock',
                ];
            @endphp
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium border {{ $statusColors[$jobPosition->status] ?? $statusColors['draft'] }}">
                <span class="material-symbols-outlined text-[16px]">{{ $statusIcons[$jobPosition->status] ?? 'info' }}</span>
                {{ $jobPosition->status_label }}
            </span>
            <a href="{{ route('hr.recruitment.positions.edit', $jobPosition) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-surface-highlight border border-[#3a2e24] rounded-lg text-white hover:bg-surface-highlight/80 transition-colors">
                <span class="material-symbols-outlined text-[18px]">edit</span>
                Modifier
            </a>
            <button wire:click="confirmDelete"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-red-400 hover:text-white hover:bg-red-500/20 border border-red-500/20 font-medium transition-colors">
                <span class="material-symbols-outlined text-[18px]">delete</span>
                Supprimer
            </button>
        </div>
    </div>

    <!-- Stats rapides -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">people</span>
                </div>
                <div>
                    <p class="text-sm text-text-secondary">Total candidatures</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-400">fiber_new</span>
                </div>
                <div>
                    <p class="text-sm text-text-secondary">Nouvelles</p>
                    <p class="text-2xl font-bold text-blue-400">{{ $stats['new'] }}</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-purple-400">record_voice_over</span>
                </div>
                <div>
                    <p class="text-sm text-text-secondary">En entretien</p>
                    <p class="text-2xl font-bold text-purple-400">{{ $stats['interview'] }}</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-green-400">how_to_reg</span>
                </div>
                <div>
                    <p class="text-sm text-text-secondary">Embauches</p>
                    <p class="text-2xl font-bold text-green-400">{{ $stats['hired'] }}</p>
                </div>
            </div>
        </x-ui.card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Colonne principale -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description du poste -->
            <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
                <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2 border-b border-[#3a2e24] pb-3">
                    <span class="material-symbols-outlined text-primary">description</span>
                    Description du poste
                </h2>
                <div class="prose prose-invert max-w-none text-text-secondary leading-relaxed">
                    {!! nl2br(e($jobPosition->description)) !!}
                </div>
            </x-ui.card>

            <!-- Prerequis -->
            @if($jobPosition->requirements)
                <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
                    <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2 border-b border-[#3a2e24] pb-3">
                        <span class="material-symbols-outlined text-primary">checklist</span>
                        Prerequis
                    </h2>
                    <div class="prose prose-invert max-w-none text-text-secondary leading-relaxed">
                        {!! nl2br(e($jobPosition->requirements)) !!}
                    </div>
                </x-ui.card>
            @endif

            <!-- Liste des candidatures -->
            <x-ui.card class="bg-surface-dark border border-[#3a2e24] p-0 overflow-hidden">
                <div class="px-6 py-4 border-b border-[#3a2e24] flex justify-between items-center">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">folder_shared</span>
                        Candidatures recentes
                    </h2>
                    <a href="{{ route('hr.recruitment.applications.index') }}?position={{ $jobPosition->id }}"
                       class="text-sm text-primary hover:text-primary/80 transition-colors flex items-center gap-1">
                        Voir tout
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-surface-highlight">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Candidat</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-text-secondary uppercase tracking-wider">Note</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-text-secondary uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-text-secondary uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#3a2e24]">
                            @forelse($jobPosition->applications()->latest()->take(5)->get() as $application)
                                <tr class="hover:bg-surface-highlight/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <a href="{{ route('hr.recruitment.applications.show', $application) }}"
                                           class="group flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center flex-shrink-0">
                                                <span class="material-symbols-outlined text-primary text-[18px]">person</span>
                                            </div>
                                            <div>
                                                <p class="font-medium text-white group-hover:text-primary transition-colors">
                                                    {{ $application->full_name }}
                                                </p>
                                                <p class="text-sm text-text-muted">{{ $application->email }}</p>
                                            </div>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($application->rating)
                                            <div class="flex items-center justify-center gap-0.5">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <span class="material-symbols-outlined text-[16px] {{ $i <= $application->rating ? 'text-yellow-400' : 'text-text-muted' }}">
                                                        star
                                                    </span>
                                                @endfor
                                            </div>
                                        @else
                                            <span class="text-text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @php
                                            $appStatusColors = [
                                                'new' => 'bg-blue-500/20 text-blue-400',
                                                'reviewing' => 'bg-yellow-500/20 text-yellow-400',
                                                'interview' => 'bg-purple-500/20 text-purple-400',
                                                'offer' => 'bg-cyan-500/20 text-cyan-400',
                                                'hired' => 'bg-green-500/20 text-green-400',
                                                'rejected' => 'bg-red-500/20 text-red-400',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $appStatusColors[$application->status] ?? 'bg-gray-500/20 text-gray-400' }}">
                                            {{ $application->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-sm text-text-secondary">{{ $application->applied_at->format('d/m/Y') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="material-symbols-outlined text-5xl text-text-muted mb-3">inbox</span>
                                            <p class="text-text-secondary">Aucune candidature pour le moment</p>
                                            <p class="text-text-muted text-sm mt-1">Les candidatures apparaitront ici une fois recues.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Informations -->
            <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2 border-b border-[#3a2e24] pb-3">
                    <span class="material-symbols-outlined text-primary">info</span>
                    Informations
                </h3>
                <dl class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-surface-highlight flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-text-secondary text-[20px]">badge</span>
                        </div>
                        <div>
                            <dt class="text-xs text-text-muted uppercase tracking-wide">Type de contrat</dt>
                            <dd class="font-medium text-white">{{ $jobPosition->type_label }}</dd>
                        </div>
                    </div>

                    @if($jobPosition->salary_range_min || $jobPosition->salary_range_max)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-surface-highlight flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-text-secondary text-[20px]">payments</span>
                            </div>
                            <div>
                                <dt class="text-xs text-text-muted uppercase tracking-wide">Fourchette salariale</dt>
                                <dd class="font-medium text-white">
                                    @if($jobPosition->salary_range_min && $jobPosition->salary_range_max)
                                        {{ number_format($jobPosition->salary_range_min, 0, ',', ' ') }} - {{ number_format($jobPosition->salary_range_max, 0, ',', ' ') }} FCFA
                                    @elseif($jobPosition->salary_range_min)
                                        A partir de {{ number_format($jobPosition->salary_range_min, 0, ',', ' ') }} FCFA
                                    @else
                                        Jusqu'a {{ number_format($jobPosition->salary_range_max, 0, ',', ' ') }} FCFA
                                    @endif
                                </dd>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-surface-highlight flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-text-secondary text-[20px]">calendar_month</span>
                        </div>
                        <div>
                            <dt class="text-xs text-text-muted uppercase tracking-wide">Publie le</dt>
                            <dd class="font-medium text-white">{{ $jobPosition->published_at?->format('d/m/Y') ?? 'Non publie' }}</dd>
                        </div>
                    </div>

                    @if($jobPosition->closes_at)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-surface-highlight flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-text-secondary text-[20px]">event_busy</span>
                            </div>
                            <div>
                                <dt class="text-xs text-text-muted uppercase tracking-wide">Date de cloture</dt>
                                <dd class="font-medium {{ $jobPosition->closes_at->isPast() ? 'text-red-400' : 'text-white' }}">
                                    {{ $jobPosition->closes_at->format('d/m/Y') }}
                                    @if($jobPosition->closes_at->isPast())
                                        <span class="text-xs text-red-400">(Expire)</span>
                                    @endif
                                </dd>
                            </div>
                        </div>
                    @endif
                </dl>
            </x-ui.card>

            <!-- Pipeline -->
            <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2 border-b border-[#3a2e24] pb-3">
                    <span class="material-symbols-outlined text-primary">analytics</span>
                    Pipeline
                </h3>
                <div class="space-y-3">
                    @php
                        $pipeline = [
                            ['key' => 'new', 'label' => 'Nouvelles', 'color' => 'blue', 'icon' => 'fiber_new'],
                            ['key' => 'reviewing', 'label' => 'En revue', 'color' => 'yellow', 'icon' => 'visibility'],
                            ['key' => 'interview', 'label' => 'Entretien', 'color' => 'purple', 'icon' => 'record_voice_over'],
                            ['key' => 'offer', 'label' => 'Offre', 'color' => 'cyan', 'icon' => 'mail'],
                            ['key' => 'hired', 'label' => 'Embauche', 'color' => 'green', 'icon' => 'how_to_reg'],
                            ['key' => 'rejected', 'label' => 'Rejete', 'color' => 'red', 'icon' => 'person_off'],
                        ];
                    @endphp
                    @foreach($pipeline as $stage)
                        <div class="flex items-center justify-between p-2 rounded-lg hover:bg-surface-highlight/50 transition-colors">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-{{ $stage['color'] }}-400 text-[18px]">{{ $stage['icon'] }}</span>
                                <span class="text-sm text-text-secondary">{{ $stage['label'] }}</span>
                            </div>
                            <span class="text-sm font-medium text-white bg-surface-highlight px-2 py-0.5 rounded">
                                {{ $stats[$stage['key']] ?? 0 }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>

            <!-- Actions -->
            <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2 border-b border-[#3a2e24] pb-3">
                    <span class="material-symbols-outlined text-primary">bolt</span>
                    Actions
                </h3>
                <div class="space-y-3">
                    @if($jobPosition->status === 'draft')
                        <button wire:click="publish"
                                wire:confirm="Publier cette offre d'emploi ?"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                            <span class="material-symbols-outlined text-[20px]">public</span>
                            Publier l'offre
                        </button>
                    @endif

                    @if($jobPosition->status === 'published')
                        <button wire:click="close"
                                wire:confirm="Cloturer cette offre ? Aucune nouvelle candidature ne sera acceptee."
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium">
                            <span class="material-symbols-outlined text-[20px]">lock</span>
                            Cloturer l'offre
                        </button>

                        <a href="{{ route('careers.apply', $jobPosition) }}" target="_blank"
                           class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-surface-highlight border border-[#3a2e24] text-white rounded-lg hover:bg-surface-highlight/80 transition-colors font-medium">
                            <span class="material-symbols-outlined text-[20px]">open_in_new</span>
                            Page candidature
                        </a>
                    @endif

                    <a href="{{ route('hr.recruitment.positions.index') }}"
                       class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-transparent border border-[#3a2e24] text-text-secondary rounded-lg hover:bg-surface-highlight/50 hover:text-white transition-colors font-medium">
                        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                        Retour a la liste
                    </a>
                </div>
            </x-ui.card>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6 max-w-md w-full shadow-xl">
                <div class="flex items-center gap-3 mb-4">
                    <div class="size-12 rounded-full bg-red-500/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-red-400 text-2xl">warning</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Confirmer la suppression</h3>
                        <p class="text-text-secondary text-sm">Cette action est irreversible</p>
                    </div>
                </div>
                <p class="text-text-secondary mb-6">
                    Etes-vous sur de vouloir supprimer le poste <strong class="text-white">{{ $jobPosition->title }}</strong> ?
                    @if($jobPosition->applications->count() > 0)
                        <br><br>
                        <span class="text-red-400 text-sm">Ce poste a {{ $jobPosition->applications->count() }} candidature(s) et ne peut pas etre supprime.</span>
                    @endif
                </p>
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete"
                        class="px-4 py-2 rounded-xl border border-[#3a2e24] text-text-secondary hover:text-white hover:bg-surface-highlight font-medium transition-colors">
                        Annuler
                    </button>
                    @if($jobPosition->applications->count() == 0)
                    <button wire:click="deleteJobPosition"
                        class="px-4 py-2 rounded-xl bg-red-500 text-white hover:bg-red-600 font-medium transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                        Supprimer
                    </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
