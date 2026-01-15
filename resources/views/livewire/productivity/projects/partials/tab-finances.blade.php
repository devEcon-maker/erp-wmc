<div class="space-y-6">
    <!-- Stats Cards - 4 indicateurs principaux -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Budget -->
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <p class="text-text-secondary">Budget</p>
                <span class="material-symbols-outlined text-primary">account_balance</span>
            </div>
            <p class="text-2xl font-bold text-white">
                {{ $financeStats['budget'] ? number_format($financeStats['budget'], 0, ',', ' ') . ' FCFA' : '-' }}
            </p>
            @if($financeStats['budget'] && $financeStats['budget_usage'] > 0)
                <div class="mt-3">
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-text-secondary">Utilisation</span>
                        <span class="{{ $financeStats['budget_usage'] > 100 ? 'text-red-400' : 'text-white' }}">
                            {{ $financeStats['budget_usage'] }}%
                        </span>
                    </div>
                    <div class="w-full h-1.5 bg-surface-highlight rounded-full overflow-hidden">
                        <div class="h-full {{ $financeStats['budget_usage'] > 100 ? 'bg-red-500' : 'bg-primary' }} rounded-full"
                            style="width: {{ min($financeStats['budget_usage'], 100) }}%"></div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Gap -->
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <p class="text-text-secondary">Gap</p>
                <span class="material-symbols-outlined {{ $financeStats['gap'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                    {{ $financeStats['gap'] >= 0 ? 'trending_up' : 'trending_down' }}
                </span>
            </div>
            <p class="text-2xl font-bold {{ $financeStats['gap'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                {{ number_format($financeStats['gap'], 0, ',', ' ') }} FCFA
            </p>
            <p class="text-xs text-text-secondary mt-2">
                Budget - (Déboursé + Coûts)
            </p>
        </div>

        <!-- Déboursé Sec -->
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <p class="text-text-secondary">Déboursé Sec</p>
                <span class="material-symbols-outlined text-orange-400">payments</span>
            </div>
            <p class="text-2xl font-bold text-orange-400">
                {{ number_format($financeStats['supplier_cost'], 0, ',', ' ') }} FCFA
            </p>
            <p class="text-xs text-text-secondary mt-2">
                Coût prestataire/fournisseur
            </p>
        </div>

        <!-- Montant Facturé -->
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <p class="text-text-secondary">Montant Facturé</p>
                <span class="material-symbols-outlined text-green-400">receipt_long</span>
            </div>
            <p class="text-2xl font-bold text-green-400">
                {{ number_format($financeStats['invoiced_amount'], 0, ',', ' ') }} FCFA
            </p>
            <p class="text-xs text-text-secondary mt-2">
                Total factures émises
            </p>
        </div>
    </div>

    <!-- Profit Analysis -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">analytics</span>
                Rentabilité
            </h3>

            <div class="space-y-4">
                <div class="flex justify-between items-center p-4 bg-surface-highlight rounded-lg">
                    <span class="text-text-secondary">Profit estimé</span>
                    <span class="text-2xl font-bold {{ $financeStats['profit'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ number_format($financeStats['profit'], 0, ',', ' ') }} FCFA
                    </span>
                </div>

                <div class="flex justify-between items-center p-4 bg-surface-highlight rounded-lg">
                    <span class="text-text-secondary">Marge</span>
                    <span class="text-2xl font-bold {{ $financeStats['profit_margin'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ $financeStats['profit_margin'] }}%
                    </span>
                </div>

                @if($project->billing_type === 'hourly' && $project->hourly_rate)
                    <div class="flex justify-between items-center p-4 bg-surface-highlight rounded-lg">
                        <span class="text-text-secondary">Taux horaire</span>
                        <span class="text-xl font-medium text-white">
                            {{ number_format($project->hourly_rate, 0, ',', ' ') }} FCFA/h
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">pie_chart</span>
                Répartition des coûts
            </h3>

            @if($project->members->count() > 0)
                <div class="space-y-3">
                    @foreach($project->members as $member)
                        @php
                            $memberCost = $project->timeEntries
                                ->where('employee_id', $member->id)
                                ->sum(fn($e) => $e->cost);
                            $percentage = $financeStats['total_cost'] > 0
                                ? round(($memberCost / $financeStats['total_cost']) * 100)
                                : 0;
                        @endphp
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm text-white">{{ $member->full_name }}</span>
                                <span class="text-sm text-text-secondary">
                                    {{ number_format($memberCost, 0, ',', ' ') }} FCFA ({{ $percentage }}%)
                                </span>
                            </div>
                            <div class="w-full h-2 bg-surface-highlight rounded-full overflow-hidden">
                                <div class="h-full bg-primary rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-text-secondary">
                    <p>Aucune donnée disponible</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Invoices -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">receipt_long</span>
                Factures liées
            </h3>
            @if($project->billing_type !== 'non_billable')
                <a href="{{ route('finance.invoices.create', ['project_id' => $project->id]) }}"
                    class="text-sm text-primary hover:text-primary/80 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Créer une facture
                </a>
            @endif
        </div>

        @if($project->invoices && $project->invoices->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-surface-highlight">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">N°</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Date</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-text-secondary uppercase">Montant</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-text-secondary uppercase">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#3a2e24]">
                        @foreach($project->invoices as $invoice)
                            <tr class="hover:bg-surface-highlight">
                                <td class="px-4 py-3">
                                    <a href="{{ route('finance.invoices.show', $invoice) }}"
                                        class="text-primary hover:text-primary/80">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-sm text-text-secondary">
                                    {{ $invoice->issue_date?->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-white text-right font-medium">
                                    {{ number_format($invoice->total_amount, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $invoiceStatusColors = [
                                            'draft' => 'bg-gray-500/20 text-gray-400',
                                            'sent' => 'bg-blue-500/20 text-blue-400',
                                            'paid' => 'bg-green-500/20 text-green-400',
                                            'overdue' => 'bg-red-500/20 text-red-400',
                                            'cancelled' => 'bg-red-500/20 text-red-400',
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 text-xs rounded-full {{ $invoiceStatusColors[$invoice->status] ?? '' }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-text-secondary">
                <span class="material-symbols-outlined text-3xl mb-2 opacity-50">receipt_long</span>
                <p>Aucune facture liée</p>
            </div>
        @endif
    </div>
</div>
