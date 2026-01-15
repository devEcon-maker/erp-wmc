<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-white">Mes Demandes</h2>
        <a href="{{ route('hr.leaves.requests.create') }}" class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white text-sm font-bold py-2 px-4 rounded-xl transition-colors">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Nouvelle Demande
        </a>
    </div>

    @if($requests instanceof \Illuminate\Pagination\LengthAwarePaginator && $requests->count() > 0)
        <div class="bg-surface-dark border border-white/10 rounded-lg overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-white/5 border-b border-white/10 text-text-secondary">
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Période</th>
                        <th class="py-3 px-4">Durée</th>
                        <th class="py-3 px-4">Statut</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($requests as $request)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="py-3 px-4 text-white">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $request->leaveType->color ?? 'gray' }}-500/10 text-{{ $request->leaveType->color ?? 'gray' }}-500">
                                    {{ $request->leaveType->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-text-secondary">
                                {{ $request->start_date->format('d/m/Y') }} - {{ $request->end_date->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4 text-white">
                                {{ $request->days_count + 0 }} jours
                            </td>
                            <td class="py-3 px-4">
                                @if($request->status === 'pending')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-500/10 text-yellow-500">En attente</span>
                                @elseif($request->status === 'approved')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-500/10 text-green-500">Approuvé</span>
                                @elseif($request->status === 'rejected')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-500/10 text-red-500">Refusé</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-500/10 text-gray-500">Annulé</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if($request->status === 'pending')
                                    <button wire:click="cancel({{ $request->id }})"
                                        wire:confirm="Êtes-vous sûr de vouloir annuler cette demande ?"
                                        class="text-red-400 hover:text-red-300 text-xs">
                                        Annuler
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $requests->links() }}
        </div>
    @else
        <div class="text-center py-12 bg-surface-dark border border-white/10 rounded-lg">
            <span class="material-symbols-outlined text-5xl text-text-secondary">calendar_month</span>
            <h3 class="mt-2 text-sm font-medium text-white">Aucune demande</h3>
            <p class="mt-1 text-sm text-text-secondary">Vous n'avez pas encore fait de demande de congés.</p>
        </div>
    @endif
</div>
