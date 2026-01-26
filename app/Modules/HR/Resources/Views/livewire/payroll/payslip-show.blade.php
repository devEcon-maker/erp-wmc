<div>
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
            <a href="{{ route('hr.payroll.periods.index') }}" class="hover:text-primary-600">Périodes de paie</a>
            <span>/</span>
            <a href="{{ route('hr.payroll.periods.show', $payslip->payrollPeriod) }}" class="hover:text-primary-600">{{ $payslip->payrollPeriod->name }}</a>
            <span>/</span>
            <span>{{ $payslip->employee->full_name }}</span>
        </div>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Bulletin de paie</h1>
            <div class="flex gap-2">
                <button wire:click="downloadPdf"
                        class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Télécharger PDF
                </button>
                <button wire:click="sendByEmail"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Envoyer par email
                </button>
            </div>
        </div>
    </div>

    <!-- Bulletin de paie -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- En-tête -->
        <div class="border-b border-gray-200 dark:border-gray-700 p-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Entreprise</h2>
                    <p class="text-gray-600 dark:text-gray-400">{{ config('app.name') }}</p>
                    <p class="text-sm text-gray-500">Cameroun</p>
                </div>
                <div class="text-right">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Période</h2>
                    <p class="text-gray-600 dark:text-gray-400">{{ $payslip->payrollPeriod->name }}</p>
                    <p class="text-sm text-gray-500">
                        Du {{ $payslip->payrollPeriod->start_date->format('d/m/Y') }}
                        au {{ $payslip->payrollPeriod->end_date->format('d/m/Y') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Informations employé -->
        <div class="border-b border-gray-200 dark:border-gray-700 p-6 bg-gray-50 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Informations employé</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Nom complet</span>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $payslip->employee->full_name }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Matricule</span>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $payslip->employee->employee_id }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Poste</span>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $payslip->employee->job_title ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Département</span>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $payslip->employee->department?->name ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Salaire de base -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Salaire de base</h3>
            <div class="flex justify-between items-center py-2">
                <div>
                    <span class="text-gray-900 dark:text-white">Salaire de base mensuel</span>
                    <span class="text-sm text-gray-500 ml-2">({{ $payslip->days_worked ?? '-' }} jours travaillés)</span>
                </div>
                <span class="font-medium text-gray-900 dark:text-white">{{ number_format($payslip->base_salary, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        <!-- Primes et avantages -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Primes et avantages</h3>
            @forelse($payslip->bonuses as $bonus)
                <div class="flex justify-between items-center py-2">
                    <span class="text-gray-900 dark:text-white">{{ $bonus->bonusType->name }}</span>
                    <span class="font-medium text-green-600">+{{ number_format($bonus->amount, 0, ',', ' ') }} FCFA</span>
                </div>
            @empty
                <p class="text-gray-500 dark:text-gray-400 text-sm">Aucune prime ce mois</p>
            @endforelse

            @if($payslip->total_bonuses > 0)
                <div class="flex justify-between items-center py-2 mt-2 border-t border-gray-200 dark:border-gray-700 font-semibold">
                    <span class="text-gray-900 dark:text-white">Total primes</span>
                    <span class="text-green-600">+{{ number_format($payslip->total_bonuses, 0, ',', ' ') }} FCFA</span>
                </div>
            @endif
        </div>

        <!-- Salaire brut -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-blue-50 dark:bg-blue-900/20">
            <div class="flex justify-between items-center">
                <span class="text-lg font-semibold text-gray-900 dark:text-white">SALAIRE BRUT</span>
                <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($payslip->gross_salary, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        <!-- Déductions -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Déductions salariales</h3>

            @foreach($payslip->deductions as $deduction)
                <div class="flex justify-between items-center py-2">
                    <div>
                        <span class="text-gray-900 dark:text-white">{{ $deduction->deductionType->name }}</span>
                        @if($deduction->deductionType->rate)
                            <span class="text-sm text-gray-500 ml-2">({{ $deduction->deductionType->rate }}%)</span>
                        @endif
                    </div>
                    <span class="font-medium text-red-600">-{{ number_format($deduction->amount, 0, ',', ' ') }} FCFA</span>
                </div>
            @endforeach

            @if($payslip->tax_amount > 0)
                <div class="flex justify-between items-center py-2">
                    <div>
                        <span class="text-gray-900 dark:text-white">IRPP (Impôt sur le revenu)</span>
                    </div>
                    <span class="font-medium text-red-600">-{{ number_format($payslip->tax_amount, 0, ',', ' ') }} FCFA</span>
                </div>
            @endif

            @if($payslip->advances_deduction > 0)
                <div class="flex justify-between items-center py-2">
                    <span class="text-gray-900 dark:text-white">Remboursement avance</span>
                    <span class="font-medium text-red-600">-{{ number_format($payslip->advances_deduction, 0, ',', ' ') }} FCFA</span>
                </div>
            @endif

            @if($payslip->loan_deduction > 0)
                <div class="flex justify-between items-center py-2">
                    <span class="text-gray-900 dark:text-white">Remboursement prêt</span>
                    <span class="font-medium text-red-600">-{{ number_format($payslip->loan_deduction, 0, ',', ' ') }} FCFA</span>
                </div>
            @endif

            <div class="flex justify-between items-center py-2 mt-2 border-t border-gray-200 dark:border-gray-700 font-semibold">
                <span class="text-gray-900 dark:text-white">Total déductions</span>
                <span class="text-red-600">-{{ number_format($payslip->total_deductions, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        <!-- Net à payer -->
        <div class="p-6 bg-green-50 dark:bg-green-900/20">
            <div class="flex justify-between items-center">
                <span class="text-xl font-bold text-gray-900 dark:text-white">NET À PAYER</span>
                <span class="text-2xl font-bold text-green-600">{{ number_format($payslip->net_salary, 0, ',', ' ') }} FCFA</span>
            </div>
            @if($payslip->paid_at)
                <p class="text-sm text-green-600 mt-2">Payé le {{ $payslip->paid_at->format('d/m/Y') }}</p>
            @endif
        </div>

        <!-- Charges patronales -->
        <div class="p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Charges patronales (pour information)</h3>
            <div class="flex justify-between items-center py-2">
                <span class="text-gray-600 dark:text-gray-400">Cotisations CNPS employeur</span>
                <span class="font-medium text-gray-600 dark:text-gray-400">{{ number_format($payslip->employer_contributions, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="flex justify-between items-center py-2 font-semibold">
                <span class="text-gray-900 dark:text-white">Coût total employeur</span>
                <span class="text-gray-900 dark:text-white">{{ number_format($payslip->gross_salary + $payslip->employer_contributions, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>
    </div>
</div>
