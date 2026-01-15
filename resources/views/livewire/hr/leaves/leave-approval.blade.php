<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h1 class="text-2xl font-bold text-white">Approbation des conges</h1>
            <p class="text-text-secondary text-sm">Gerez les demandes de conges de votre equipe</p>
        </div>
        @if($pendingRequests instanceof \Illuminate\Pagination\LengthAwarePaginator && $pendingRequests->total() > 0)
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium bg-yellow-500/20 text-yellow-400 border border-yellow-500/30">
                <span class="material-symbols-outlined text-[18px]">pending_actions</span>
                {{ $pendingRequests->total() }} en attente
            </span>
        @endif
    </div>

    <!-- Liste des demandes -->
    <x-ui.card class="bg-surface-dark border border-[#3a2e24] p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-[#3a2e24]">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">inbox</span>
                Demandes a approuver
            </h2>
        </div>

        @if($pendingRequests instanceof \Illuminate\Pagination\LengthAwarePaginator && $pendingRequests->count() > 0)
            <div class="divide-y divide-[#3a2e24]">
                @foreach($pendingRequests as $request)
                    <div class="p-6 hover:bg-surface-highlight/30 transition-colors">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <!-- Info employe -->
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold">
                                    {{ substr($request->employee->first_name, 0, 1) }}{{ substr($request->employee->last_name, 0, 1) }}
                                </div>
                                <div>
                                    <h3 class="font-medium text-white">{{ $request->employee->full_name }}</h3>
                                    <p class="text-sm text-text-secondary">{{ $request->employee->position ?? 'Employe' }}</p>
                                </div>
                            </div>

                            <!-- Details conge -->
                            <div class="flex flex-wrap items-center gap-4 md:gap-6">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-text-muted text-[18px]">category</span>
                                    <span class="text-sm text-white">{{ $request->leaveType->name }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-text-muted text-[18px]">event</span>
                                    <span class="text-sm text-white">
                                        {{ $request->start_date->format('d/m') }} - {{ $request->end_date->format('d/m/Y') }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-text-muted text-[18px]">schedule</span>
                                    <span class="text-sm font-medium text-primary">{{ $request->days_count + 0 }} jour(s)</span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2">
                                <button wire:click="reject({{ $request->id }})"
                                        wire:loading.attr="disabled"
                                        wire:confirm="Refuser cette demande de conge ?"
                                        class="flex items-center gap-2 px-4 py-2 bg-red-500/10 text-red-400 border border-red-500/30 rounded-lg hover:bg-red-500/20 transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">close</span>
                                    Refuser
                                </button>
                                <button wire:click="approve({{ $request->id }})"
                                        wire:loading.attr="disabled"
                                        wire:confirm="Approuver cette demande de conge ?"
                                        class="flex items-center gap-2 px-4 py-2 bg-green-500/10 text-green-400 border border-green-500/30 rounded-lg hover:bg-green-500/20 transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">check</span>
                                    Approuver
                                </button>
                            </div>
                        </div>

                        @if($request->reason)
                            <div class="mt-4 ml-16 p-3 bg-surface-highlight rounded-lg border border-[#3a2e24]">
                                <p class="text-sm text-text-secondary flex items-start gap-2">
                                    <span class="material-symbols-outlined text-[16px] mt-0.5">format_quote</span>
                                    {{ $request->reason }}
                                </p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="px-6 py-4 border-t border-[#3a2e24]">
                {{ $pendingRequests->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <div class="w-16 h-16 mx-auto rounded-full bg-green-500/20 flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-green-400 text-3xl">check_circle</span>
                </div>
                <h3 class="text-lg font-medium text-white mb-2">Tout est a jour</h3>
                <p class="text-text-secondary">Aucune demande de conge en attente d'approbation.</p>
            </div>
        @endif
    </x-ui.card>

    @if(session('success'))
        <div class="fixed bottom-4 right-4 p-4 bg-green-500/20 border border-green-500/30 rounded-lg text-green-400 flex items-center gap-2"
             x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 3000)">
            <span class="material-symbols-outlined">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
</div>
