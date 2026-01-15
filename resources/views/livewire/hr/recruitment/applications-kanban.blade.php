<div class="space-y-6">
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white">Candidatures</h2>
            <p class="text-text-secondary text-sm">Gérez les candidatures par glisser-déposer.</p>
        </div>
        <div class="flex gap-2">
            <x-ui.select wire:model.live="jobPositionId" class="w-64">
                <option value="">Tous les postes</option>
                @foreach($positions as $position)
                    <option value="{{ $position->id }}">{{ $position->title }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.button href="{{ route('hr.recruitment.positions.index') }}" type="secondary" class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">work</span>
                Offres
            </x-ui.button>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="flex space-x-4 overflow-x-auto pb-4">
        @foreach($statuses as $status => $config)
            @php
                $statusApplications = $applicationsByStatus[$status] ?? collect([]);
                $colorClasses = [
                    'blue' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
                    'yellow' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
                    'purple' => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
                    'green' => 'bg-green-500/20 text-green-400 border-green-500/30',
                    'emerald' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                    'red' => 'bg-red-500/20 text-red-400 border-red-500/30',
                ];
                $headerColor = $colorClasses[$config['color']] ?? 'bg-gray-500/20 text-gray-400';
            @endphp

            <div class="flex-shrink-0 w-72 bg-surface-dark border border-[#3a2e24] rounded-xl"
                x-data
                x-on:drop="$wire.updateStatus($event.dataTransfer.getData('application'), '{{ $status }}')"
                x-on:dragover.prevent>

                <div class="flex justify-between items-center p-4 border-b border-[#3a2e24]">
                    <h3 class="font-semibold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ str_replace(['text-', '/20', '/30'], ['bg-', '', ''], $headerColor) }}"></span>
                        {{ $config['label'] }}
                    </h3>
                    <span class="px-2 py-0.5 text-xs rounded-full {{ $headerColor }}">
                        {{ count($statusApplications) }}
                    </span>
                </div>

                <div class="p-3 space-y-3 min-h-[200px]">
                    @foreach($statusApplications as $application)
                        <div class="bg-surface-highlight border border-[#3a2e24] rounded-lg p-4 cursor-grab hover:border-primary/50 transition-colors"
                            draggable="true"
                            x-on:dragstart="$event.dataTransfer.setData('application', {{ $application->id }})">

                            <a href="{{ route('hr.recruitment.applications.show', $application) }}" class="block">
                                <h4 class="font-medium text-white hover:text-primary transition-colors">
                                    {{ $application->first_name ?? '' }} {{ $application->last_name ?? '' }}
                                </h4>
                            </a>

                            <p class="text-sm text-text-secondary mt-1">
                                <span class="material-symbols-outlined text-[14px] align-middle mr-1">work</span>
                                {{ $application->jobPosition->title ?? '-' }}
                            </p>

                            <div class="flex justify-between items-center mt-3">
                                <span class="text-xs text-text-secondary">
                                    <span class="material-symbols-outlined text-[12px] align-middle mr-1">schedule</span>
                                    {{ $application->applied_at ? $application->applied_at->diffForHumans() : '-' }}
                                </span>
                                @if($application->rating)
                                    <span class="text-xs text-yellow-500">
                                        @for($i = 0; $i < $application->rating; $i++)
                                            <span class="material-symbols-outlined text-[14px]">star</span>
                                        @endfor
                                    </span>
                                @endif
                            </div>

                            <!-- Quick actions -->
                            <div class="mt-3 pt-3 border-t border-[#3a2e24] flex justify-between">
                                @if($application->resume_path)
                                    <a href="{{ Storage::url($application->resume_path) }}" target="_blank"
                                        class="text-xs text-primary hover:text-primary/80 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">description</span>
                                        CV
                                    </a>
                                @endif
                                <a href="mailto:{{ $application->email }}"
                                    class="text-xs text-text-secondary hover:text-white flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">mail</span>
                                    Email
                                </a>
                                @if($application->phone)
                                    <a href="tel:{{ $application->phone }}"
                                        class="text-xs text-text-secondary hover:text-white flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">call</span>
                                        Appeler
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @if(count($statusApplications) === 0)
                        <div class="text-center py-8 text-text-secondary text-sm">
                            <span class="material-symbols-outlined text-3xl mb-2 opacity-50">inbox</span>
                            <p>Aucune candidature</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="text-sm text-text-secondary flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">info</span>
        Glissez-déposez les candidatures pour changer leur statut.
    </div>
</div>
