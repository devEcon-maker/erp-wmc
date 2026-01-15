<div>
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
            <a href="{{ route('hr.payroll.periods.index') }}" class="hover:text-primary-600">Périodes de paie</a>
            <span>/</span>
            <span>{{ $payrollPeriod->name }}</span>
        </div>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $payrollPeriod->name }}</h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Du {{ $payrollPeriod->start_date->format('d/m/Y') }} au {{ $payrollPeriod->end_date->format('d/m/Y') }}
                </p>
            </div>
            <span class="px-3 py-1 text-sm font-medium rounded-full
                @if($payrollPeriod->status === 'draft') bg-gray-100 text-gray-800
                @elseif($payrollPeriod->status === 'validated') bg-blue-100 text-blue-800
                @elseif($payrollPeriod->status === 'paid') bg-green-100 text-green-800
                @else bg-red-100 text-red-800
                @endif">
                {{ \App\Modules\HR\Models\PayrollPeriod::STATUSES[$payrollPeriod->status] ?? $payrollPeriod->status }}
            </span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">Employés</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_employees'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">Salaire brut total</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_gross'], 0, ',', ' ') }}</div>
            <div class="text-xs text-gray-500">FCFA</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">Salaire net total</div>
            <div class="text-2xl font-bold text-green-600">{{ number_format($stats['total_net'], 0, ',', ' ') }}</div>
            <div class="text-xs text-gray-500">FCFA</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">Charges patronales</div>
            <div class="text-2xl font-bold text-orange-600">{{ number_format($stats['total_employer_charges'], 0, ',', ' ') }}</div>
            <div class="text-xs text-gray-500">FCFA</div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-wrap gap-3 mb-6">
        @if($payrollPeriod->status === 'draft')
            <button wire:click="generateAllPayslips"
                    wire:confirm="Générer les bulletins pour tous les employés actifs?"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Générer bulletins
            </button>
            <button wire:click="validatePeriod"
                    wire:confirm="Valider cette période? Les bulletins ne pourront plus être modifiés."
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Valider la période
            </button>
        @elseif($payrollPeriod->status === 'validated')
            <button wire:click="markPeriodAsPaid"
                    wire:confirm="Marquer tous les bulletins comme payés?"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Marquer comme payée
            </button>
        @endif
    </div>

    <!-- Filtres -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Rechercher un employé..."
                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">

            <select wire:model.live="departmentFilter"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">Tous les départements</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="statusFilter"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">Tous les statuts</option>
                <option value="draft">Brouillon</option>
                <option value="validated">Validé</option>
                <option value="paid">Payé</option>
            </select>
        </div>
    </div>

    <!-- Liste des bulletins -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Employé</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Département</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Salaire brut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Déductions</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Net à payer</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($payslips as $payslip)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                                            <span class="text-primary-600 dark:text-primary-400 font-medium text-sm">
                                                {{ substr($payslip->employee->first_name, 0, 1) }}{{ substr($payslip->employee->last_name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $payslip->employee->full_name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $payslip->employee->employee_id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $payslip->employee->department?->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                                {{ number_format($payslip->gross_salary, 0, ',', ' ') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">
                                -{{ number_format($payslip->total_deductions, 0, ',', ' ') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-green-600">
                                {{ number_format($payslip->net_salary, 0, ',', ' ') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($payslip->status === 'draft') bg-gray-100 text-gray-800
                                    @elseif($payslip->status === 'validated') bg-blue-100 text-blue-800
                                    @elseif($payslip->status === 'paid') bg-green-100 text-green-800
                                    @endif">
                                    {{ ucfirst($payslip->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('hr.payroll.payslips.show', $payslip) }}"
                                       class="text-primary-600 hover:text-primary-800">
                                        Voir
                                    </a>
                                    @if($payrollPeriod->status === 'draft')
                                        <button wire:click="regeneratePayslip({{ $payslip->id }})"
                                                wire:confirm="Régénérer ce bulletin?"
                                                class="text-blue-600 hover:text-blue-800">
                                            Régénérer
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                Aucun bulletin de paie pour cette période.
                                @if($payrollPeriod->status === 'draft')
                                    <button wire:click="generateAllPayslips" class="text-primary-600 hover:underline">
                                        Générer les bulletins
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $payslips->links() }}
    </div>
</div>
