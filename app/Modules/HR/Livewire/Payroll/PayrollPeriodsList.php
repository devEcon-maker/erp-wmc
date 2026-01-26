<?php

namespace App\Modules\HR\Livewire\Payroll;

use App\Modules\HR\Models\PayrollPeriod;
use App\Modules\HR\Services\PayrollService;
use Livewire\Component;
use Livewire\WithPagination;

class PayrollPeriodsList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $yearFilter = '';

    // Modal création période
    public $showCreateModal = false;
    public $periodForm = [
        'name' => '',
        'month' => '',
        'year' => '',
        'start_date' => '',
        'end_date' => '',
    ];

    protected $queryString = ['search', 'statusFilter', 'yearFilter'];

    public function mount()
    {
        $this->yearFilter = date('Y');
        $this->periodForm['year'] = date('Y');
        $this->periodForm['month'] = date('m');
    }

    public function updatedPeriodFormMonth()
    {
        $this->updatePeriodDates();
    }

    public function updatedPeriodFormYear()
    {
        $this->updatePeriodDates();
    }

    private function updatePeriodDates()
    {
        if ($this->periodForm['month'] && $this->periodForm['year']) {
            $month = str_pad($this->periodForm['month'], 2, '0', STR_PAD_LEFT);
            $year = $this->periodForm['year'];

            $this->periodForm['name'] = $this->getMonthName($month) . ' ' . $year;
            $this->periodForm['start_date'] = "{$year}-{$month}-01";
            $this->periodForm['end_date'] = date('Y-m-t', strtotime($this->periodForm['start_date']));
        }
    }

    private function getMonthName($month): string
    {
        $months = [
            '01' => 'Janvier', '02' => 'Février', '03' => 'Mars',
            '04' => 'Avril', '05' => 'Mai', '06' => 'Juin',
            '07' => 'Juillet', '08' => 'Août', '09' => 'Septembre',
            '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre',
        ];
        return $months[$month] ?? '';
    }

    public function openCreateModal()
    {
        $this->reset('periodForm');
        $this->periodForm['year'] = date('Y');
        $this->periodForm['month'] = date('m');
        $this->updatePeriodDates();
        $this->showCreateModal = true;
    }

    public function createPeriod()
    {
        $this->validate([
            'periodForm.name' => 'required|string|max:255',
            'periodForm.month' => 'required|numeric|between:1,12',
            'periodForm.year' => 'required|numeric|min:2020|max:2100',
            'periodForm.start_date' => 'required|date',
            'periodForm.end_date' => 'required|date|after_or_equal:periodForm.start_date',
        ]);

        // Vérifier si la période existe déjà (via les dates)
        $exists = PayrollPeriod::whereDate('start_date', $this->periodForm['start_date'])
            ->whereDate('end_date', $this->periodForm['end_date'])
            ->exists();

        if ($exists) {
            $this->addError('periodForm.month', 'Une période existe déjà pour ce mois.');
            return;
        }

        $payrollService = app(PayrollService::class);
        $payrollService->createPeriod(
            (int) $this->periodForm['month'],
            (int) $this->periodForm['year']
        );

        $this->showCreateModal = false;
        $this->dispatch('notify', type: 'success', message: 'Période de paie créée avec succès.');
    }

    public function generatePayslips($periodId)
    {
        $period = PayrollPeriod::findOrFail($periodId);

        if ($period->status !== 'draft') {
            $this->dispatch('notify', type: 'error', message: 'Cette période ne peut plus être modifiée.');
            return;
        }

        $payrollService = app(PayrollService::class);
        $result = $payrollService->generatePayslips($period);

        $successCount = $result['success'] ?? 0;
        $errors = $result['errors'] ?? [];

        if (count($errors) > 0) {
            $errorMessages = collect($errors)->map(fn($e) => $e['employee'] . ': ' . $e['error'])->join(', ');
            $this->dispatch('notify', type: 'warning', message: "{$successCount} bulletins générés. Erreurs: {$errorMessages}");
        } else {
            $this->dispatch('notify', type: 'success', message: "{$successCount} bulletins de paie générés.");
        }
    }

    public function validatePeriod($periodId)
    {
        $period = PayrollPeriod::findOrFail($periodId);

        try {
            $payrollService = app(PayrollService::class);
            $payrollService->validatePeriod($period, auth()->user());

            $this->dispatch('notify', type: 'success', message: 'Période validée avec succès.');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function markAsPaid($periodId, $paymentMethod = 'bank_transfer')
    {
        $period = PayrollPeriod::findOrFail($periodId);

        try {
            $payrollService = app(PayrollService::class);
            $payrollService->markPeriodAsPaid($period, $paymentMethod);

            $this->dispatch('notify', type: 'success', message: 'Période marquée comme payée.');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function deletePeriod($periodId)
    {
        $period = PayrollPeriod::findOrFail($periodId);

        if ($period->status !== 'draft') {
            $this->dispatch('notify', type: 'error', message: 'Seules les périodes en brouillon peuvent être supprimées.');
            return;
        }

        $period->payslips()->delete();
        $period->delete();

        $this->dispatch('notify', type: 'success', message: 'Période supprimée.');
    }

    public function render()
    {
        $periods = PayrollPeriod::query()
            ->withCount('payslips')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->yearFilter, fn($q) => $q->whereYear('start_date', $this->yearFilter))
            ->orderByDesc('start_date')
            ->paginate(12);

        $years = PayrollPeriod::selectRaw('YEAR(start_date) as year')
            ->distinct()
            ->pluck('year')
            ->sortDesc();

        return view('hr::livewire.payroll.payroll-periods-list', [
            'periods' => $periods,
            'years' => $years,
            'statuses' => PayrollPeriod::STATUSES,
        ])->layout('layouts.app');
    }
}
