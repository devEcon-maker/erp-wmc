<div class="bg-surface-dark border border-white/10 rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-white/10 bg-white/5 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-white">Demandes à approuver</h2>
        @if($pendingRequests instanceof \Illuminate\Pagination\LengthAwarePaginator && $pendingRequests->total() > 0)
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-500/10 text-yellow-500">{{ $pendingRequests->total() }} en attente</span>
        @endif
    </div>

    @if($pendingRequests instanceof \Illuminate\Pagination\LengthAwarePaginator && $pendingRequests->count() > 0)
        <ul class="divide-y divide-white/5">
            @foreach($pendingRequests as $request)
                <li class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div
                                class="w-10 h-10 rounded-full bg-primary-500/20 text-primary-500 flex items-center justify-center font-bold">
                                {{ substr($request->employee->first_name, 0, 1) }}{{ substr($request->employee->last_name, 0, 1) }}
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-white">{{ $request->employee->full_name }}</h3>
                                <p class="text-xs text-text-secondary mt-0.5">
                                    {{ $request->leaveType->name }} • {{ $request->days_count + 0 }} jours
                                </p>
                                <p class="text-xs text-text-secondary mt-0.5">
                                    Du {{ $request->start_date->format('d/m') }} au {{ $request->end_date->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button wire:click="reject({{ $request->id }})" wire:loading.attr="disabled"
                                class="p-2 text-red-400 hover:bg-red-500/10 rounded-lg transition-colors" title="Refuser">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                            <button wire:click="approve({{ $request->id }})" wire:loading.attr="disabled"
                                class="p-2 text-green-400 hover:bg-green-500/10 rounded-lg transition-colors" title="Approuver">
                                <span class="material-symbols-outlined">check</span>
                            </button>
                        </div>
                    </div>
                    @if($request->reason)
                        <div class="mt-3 ml-14 text-sm text-text-secondary italic">
                            "{{ $request->reason }}"
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
        <div class="px-6 py-4 border-t border-white/10">
            {{ $pendingRequests->links() }}
        </div>
    @else
        <div class="p-8 text-center">
            <span class="material-symbols-outlined text-5xl text-green-500/50">check_circle</span>
            <h3 class="mt-2 text-sm font-medium text-white">Tout est à jour</h3>
            <p class="mt-1 text-sm text-text-secondary">Aucune demande en attente d'approbation.</p>
        </div>
    @endif
</div>
