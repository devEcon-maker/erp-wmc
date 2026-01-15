<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Mes bulletins de paie</h1>
        <p class="text-gray-600 dark:text-gray-400">Consultez et téléchargez vos bulletins de paie</p>
    </div>

    @if(!$this->employee)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-yellow-800">
            <p>Votre compte n'est pas associé à un dossier employé. Contactez les RH.</p>
        </div>
    @else
        <!-- Stats annuelles -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
                <div class="text-sm text-gray-500 dark:text-gray-400">Bulletins {{ $yearFilter ?: date('Y') }}</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['count'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total brut {{ $yearFilter ?: date('Y') }}</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_gross'], 0, ',', ' ') }}</div>
                <div class="text-xs text-gray-500">FCFA</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total net {{ $yearFilter ?: date('Y') }}</div>
                <div class="text-2xl font-bold text-green-600">{{ number_format($stats['total_net'], 0, ',', ' ') }}</div>
                <div class="text-xs text-gray-500">FCFA</div>
            </div>
        </div>

        <!-- Filtre -->
        <div class="mb-6">
            <select wire:model.live="yearFilter"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                @foreach($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>

        <!-- Liste des bulletins -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($payslips as $payslip)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $payslip->payrollPeriod->name }}</h3>
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                @if($payslip->status === 'paid') bg-green-100 text-green-800
                                @else bg-blue-100 text-blue-800
                                @endif">
                                {{ $payslip->status === 'paid' ? 'Payé' : 'Validé' }}
                            </span>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Salaire brut</span>
                                <span>{{ number_format($payslip->gross_salary, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Déductions</span>
                                <span class="text-red-600">-{{ number_format($payslip->total_deductions, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="flex justify-between font-semibold text-gray-900 dark:text-white pt-2 border-t border-gray-200 dark:border-gray-700">
                                <span>Net à payer</span>
                                <span class="text-green-600">{{ number_format($payslip->net_salary, 0, ',', ' ') }} FCFA</span>
                            </div>
                        </div>

                        @if($payslip->paid_at)
                            <p class="text-xs text-gray-500 mt-2">Payé le {{ $payslip->paid_at->format('d/m/Y') }}</p>
                        @endif
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900">
                        <a href="{{ route('hr.payroll.payslips.show', $payslip) }}"
                           class="text-sm text-primary-600 hover:text-primary-800 font-medium">
                            Voir le détail
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 bg-white dark:bg-gray-800 rounded-lg">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucun bulletin de paie</h3>
                    <p class="mt-1 text-sm text-gray-500">Vos bulletins apparaîtront ici une fois générés.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $payslips->links() }}
        </div>
    @endif
</div>
