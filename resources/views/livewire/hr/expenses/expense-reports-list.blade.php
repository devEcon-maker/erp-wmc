<div class="space-y-6">
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white">Notes de frais</h2>
            <p class="text-text-secondary text-sm">Gestion des dépenses et remboursements.</p>
        </div>
        <div class="flex gap-2">
            @can('expenses.approve')
                <x-ui.button wire:click="togglePendingApproval" type="{{ $showPendingApproval ? 'primary' : 'secondary' }}"
                    class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">pending_actions</span>
                    À approuver
                    @if($stats['pending_approval'] > 0)
                        <span class="ml-1 px-2 py-0.5 text-xs bg-yellow-500/20 text-yellow-400 rounded-full">
                            {{ $stats['pending_approval'] }}
                        </span>
                    @endif
                </x-ui.button>
            @endcan
            <x-ui.button href="{{ route('hr.expenses.create') }}" type="primary"
                class="flex items-center gap-2 shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-[20px]">add_circle</span>
                Nouvelle Note
            </x-ui.button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">Brouillons</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['draft'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-gray-500">edit_note</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">Soumises</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ $stats['submitted'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-yellow-500/50">schedule</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">Approuvées</p>
                    <p class="text-2xl font-bold text-green-400">{{ $stats['approved'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-green-500/50">check_circle</span>
            </div>
        </div>
        @can('expenses.approve')
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-text-secondary text-sm">En attente</p>
                        <p class="text-2xl font-bold text-primary">{{ $stats['pending_approval'] }}</p>
                    </div>
                    <span class="material-symbols-outlined text-3xl text-primary/50">pending_actions</span>
                </div>
            </div>
        @endcan
    </div>

    <!-- Filters -->
    <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-ui.input wire:model.live.debounce.300ms="search" label="Recherche"
                placeholder="Référence..." />

            <x-ui.select wire:model.live="status" label="Statut">
                <option value="">Tous les statuts</option>
                <option value="draft">Brouillon</option>
                <option value="submitted">Soumis</option>
                <option value="approved">Approuvé</option>
                <option value="rejected">Rejeté</option>
                <option value="paid">Payé</option>
            </x-ui.select>

            @can('expenses.approve')
                <x-ui.select wire:model.live="employeeId" label="Employé">
                    <option value="">Tous les employés</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                    @endforeach
                </x-ui.select>
            @endcan
        </div>
    </x-ui.card>

    <!-- Table -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#3a2e24]">
                <thead class="bg-surface-highlight">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Référence</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Employé</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Période</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Montant</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#3a2e24]">
                    @forelse($reports as $report)
                        <tr class="hover:bg-surface-highlight/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('hr.expenses.show', $report) }}"
                                    class="text-primary hover:text-primary/80 font-bold font-mono transition-colors">
                                    {{ $report->reference ?? 'NDF-' . str_pad($report->id, 5, '0', STR_PAD_LEFT) }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center">
                                    <div class="size-8 rounded-full bg-surface-highlight flex items-center justify-center text-xs font-bold text-white mr-3 border border-[#3a2e24]">
                                        {{ substr($report->employee->first_name ?? '', 0, 1) }}{{ substr($report->employee->last_name ?? '', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-white">{{ $report->employee->full_name ?? '-' }}</div>
                                        <div class="text-xs text-text-secondary">{{ $report->employee->department->name ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">
                                <span class="material-symbols-outlined text-[14px] align-middle mr-1">calendar_month</span>
                                {{ $report->period_start->format('d/m/Y') }} - {{ $report->period_end->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-mono font-bold text-white">
                                {{ number_format($report->total_amount ?? 0, 2, ',', ' ') }} FCFA
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $statusColors = [
                                        'draft' => 'gray',
                                        'submitted' => 'yellow',
                                        'approved' => 'green',
                                        'rejected' => 'red',
                                        'paid' => 'blue',
                                    ];
                                    $statusLabels = [
                                        'draft' => 'Brouillon',
                                        'submitted' => 'Soumis',
                                        'approved' => 'Approuvé',
                                        'rejected' => 'Rejeté',
                                        'paid' => 'Payé',
                                    ];
                                @endphp
                                <x-ui.badge :color="$statusColors[$report->status] ?? 'gray'">
                                    {{ $statusLabels[$report->status] ?? ucfirst($report->status) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('hr.expenses.show', $report) }}"
                                        class="text-text-secondary hover:text-primary transition-colors" title="Voir">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </a>
                                    @if($report->status === 'draft')
                                        <a href="{{ route('hr.expenses.edit', $report) }}"
                                            class="text-text-secondary hover:text-yellow-500 transition-colors" title="Modifier">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-text-secondary">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">receipt_long</span>
                                    <p>Aucune note de frais trouvée.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reports->hasPages())
            <div class="px-4 py-3 border-t border-[#3a2e24]">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</div>
