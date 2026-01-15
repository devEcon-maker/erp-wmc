<div class="space-y-6">
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white">Offres d'emploi</h2>
            <p class="text-text-secondary text-sm">Gestion des postes et recrutement.</p>
        </div>
        <div class="flex gap-2">
            <x-ui.button href="{{ route('hr.recruitment.applications.index') }}" type="secondary" class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">group</span>
                Candidatures
            </x-ui.button>
            <x-ui.button href="{{ route('hr.recruitment.positions.create') }}" type="primary"
                class="flex items-center gap-2 shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-[20px]">add_circle</span>
                Nouveau Poste
            </x-ui.button>
        </div>
    </div>

    <!-- Filters -->
    <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-ui.input wire:model.live.debounce.300ms="search" label="Recherche"
                placeholder="Titre du poste..." />

            <x-ui.select wire:model.live="status" label="Statut">
                <option value="">Tous les statuts</option>
                <option value="draft">Brouillon</option>
                <option value="published">Publié</option>
                <option value="closed">Fermé</option>
            </x-ui.select>

            <x-ui.select wire:model.live="department_id" label="Département">
                <option value="">Tous les départements</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select wire:model.live="type" label="Type de contrat">
                <option value="">Tous les types</option>
                <option value="full_time">Temps plein (CDI)</option>
                <option value="part_time">Temps partiel</option>
                <option value="contract">CDD</option>
                <option value="internship">Stage</option>
            </x-ui.select>
        </div>
    </x-ui.card>

    <!-- Table -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#3a2e24]">
                <thead class="bg-surface-highlight">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Poste</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Département</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Type</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Candidatures</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#3a2e24]">
                    @forelse($positions as $position)
                        <tr class="hover:bg-surface-highlight/50 transition-colors">
                            <td class="px-6 py-4">
                                <a href="{{ route('hr.recruitment.positions.show', $position) }}"
                                    class="text-primary hover:text-primary/80 font-bold transition-colors">
                                    {{ $position->title }}
                                </a>
                                @if($position->location)
                                    <p class="text-xs text-text-secondary mt-0.5">
                                        <span class="material-symbols-outlined text-[14px] align-middle mr-1">location_on</span>
                                        {{ $position->location }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                {{ $position->department->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $typeLabels = [
                                        'full_time' => 'CDI',
                                        'part_time' => 'Temps partiel',
                                        'contract' => 'CDD',
                                        'internship' => 'Stage',
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs rounded bg-surface-highlight text-text-secondary border border-[#3a2e24]">
                                    {{ $typeLabels[$position->type] ?? $position->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="font-bold text-white">{{ $position->applications_count ?? 0 }}</span>
                                @if(($position->applications()->where('status', 'new')->count() ?? 0) > 0)
                                    <span class="ml-1 px-2 py-0.5 text-xs bg-blue-500/20 text-blue-400 rounded-full">
                                        {{ $position->applications()->where('status', 'new')->count() }} nouvelles
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $statusColors = [
                                        'draft' => 'gray',
                                        'published' => 'green',
                                        'closed' => 'red',
                                    ];
                                    $statusLabels = [
                                        'draft' => 'Brouillon',
                                        'published' => 'Publié',
                                        'closed' => 'Fermé',
                                    ];
                                @endphp
                                <x-ui.badge :color="$statusColors[$position->status] ?? 'gray'">
                                    {{ $statusLabels[$position->status] ?? ucfirst($position->status) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('hr.recruitment.positions.show', $position) }}"
                                        class="text-text-secondary hover:text-primary transition-colors" title="Voir">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </a>
                                    <a href="{{ route('hr.recruitment.positions.edit', $position) }}"
                                        class="text-text-secondary hover:text-yellow-500 transition-colors" title="Modifier">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-text-secondary">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">work_off</span>
                                    <p>Aucune offre d'emploi trouvée.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($positions->hasPages())
            <div class="px-4 py-3 border-t border-[#3a2e24]">
                {{ $positions->links() }}
            </div>
        @endif
    </div>
</div>
