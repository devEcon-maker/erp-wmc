<div class="space-y-6">
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white">Organigramme</h2>
            <p class="text-text-secondary text-sm">Structure organisationnelle par département.</p>
        </div>
        <x-ui.button href="{{ route('hr.employees.index') }}" type="secondary" class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">list</span>
            Liste
        </x-ui.button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($departments as $dept)
            <x-ui.card class="bg-surface-dark border border-[#3a2e24] h-full">
                <div class="flex items-center justify-between mb-4 border-b border-[#3a2e24] pb-2">
                    <h3 class="text-lg font-bold text-white">{{ $dept->name }}</h3>
                    <span
                        class="text-xs bg-surface-highlight border border-[#3a2e24] text-text-secondary px-2 py-1 rounded-full">
                        {{ $dept->employees->count() }} pers.
                    </span>
                </div>

                <!-- Manager -->
                <div class="mb-6">
                    <div class="text-xs text-text-secondary uppercase tracking-wider mb-2 font-bold">Responsable</div>
                    @if($dept->manager)
                        <a href="{{ route('hr.employees.show', $dept->manager) }}"
                            class="flex items-center gap-3 p-2 bg-primary/10 border border-primary/20 rounded-xl">
                            <div
                                class="size-8 rounded-full bg-primary text-white flex items-center justify-center text-xs font-bold">
                                {{ substr($dept->manager->first_name, 0, 1) }}{{ substr($dept->manager->last_name, 0, 1) }}
                            </div>
                            <div>
                                <div class="text-sm font-bold text-white">{{ $dept->manager->full_name }}</div>
                                <div class="text-[10px] text-primary">{{ $dept->manager->job_title }}</div>
                            </div>
                        </a>
                    @else
                        <div class="text-sm text-text-secondary italic pl-2">Poste vacant</div>
                    @endif
                </div>

                <!-- Team -->
                <div>
                    <div class="text-xs text-text-secondary uppercase tracking-wider mb-2 font-bold">Équipe</div>
                    <div class="space-y-2 max-h-[300px] overflow-y-auto custom-scrollbar pr-1">
                        @forelse($dept->employees->where('id', '!=', $dept->manager_id) as $emp)
                            <a href="{{ route('hr.employees.show', $emp) }}"
                                class="flex items-center gap-3 p-2 hover:bg-surface-highlight rounded-lg group transition-colors">
                                <div
                                    class="size-8 rounded-full bg-surface-highlight border border-[#3a2e24] flex items-center justify-center text-xs font-bold text-text-secondary group-hover:text-white group-hover:border-primary/30">
                                    {{ substr($emp->first_name, 0, 1) }}{{ substr($emp->last_name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-sm text-text-secondary group-hover:text-white transition-colors">
                                        {{ $emp->full_name }}</div>
                                    <div class="text-[10px] text-text-secondary opacity-70">{{ $emp->job_title }}</div>
                                </div>
                            </a>
                        @empty
                            <div class="text-sm text-text-secondary italic pl-2">Aucun autre membre</div>
                        @endforelse
                    </div>
                </div>
            </x-ui.card>
        @endforeach
    </div>
</div>