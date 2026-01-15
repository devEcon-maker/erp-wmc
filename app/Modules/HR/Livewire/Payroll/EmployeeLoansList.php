<?php

namespace App\Modules\HR\Livewire\Payroll;

use App\Modules\HR\Models\EmployeeLoan;
use App\Modules\HR\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeLoansList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    // Modal création
    public $showCreateModal = false;
    public $loanForm = [
        'employee_id' => '',
        'amount' => '',
        'interest_rate' => 0,
        'installments' => 12,
        'reason' => '',
        'start_date' => '',
    ];

    // Modal détails
    public $showDetailModal = false;
    public $selectedLoan = null;

    protected $queryString = ['search', 'statusFilter'];

    public function mount()
    {
        $this->loanForm['start_date'] = date('Y-m-01', strtotime('+1 month'));
    }

    public function openCreateModal()
    {
        $this->reset('loanForm');
        $this->loanForm['start_date'] = date('Y-m-01', strtotime('+1 month'));
        $this->loanForm['installments'] = 12;
        $this->loanForm['interest_rate'] = 0;
        $this->showCreateModal = true;
    }

    public function getMonthlyPaymentProperty()
    {
        if (!$this->loanForm['amount'] || !$this->loanForm['installments']) {
            return 0;
        }

        $principal = (float) $this->loanForm['amount'];
        $rate = (float) $this->loanForm['interest_rate'] / 100 / 12;
        $months = (int) $this->loanForm['installments'];

        if ($rate > 0) {
            return $principal * ($rate * pow(1 + $rate, $months)) / (pow(1 + $rate, $months) - 1);
        }

        return $principal / $months;
    }

    public function createLoan()
    {
        $this->validate([
            'loanForm.employee_id' => 'required|exists:employees,id',
            'loanForm.amount' => 'required|numeric|min:10000',
            'loanForm.interest_rate' => 'required|numeric|min:0|max:50',
            'loanForm.installments' => 'required|integer|min:1|max:60',
            'loanForm.reason' => 'nullable|string|max:500',
            'loanForm.start_date' => 'required|date|after:today',
        ]);

        $employee = Employee::findOrFail($this->loanForm['employee_id']);

        // Vérifier qu'il n'y a pas de prêt actif
        $activeLoan = EmployeeLoan::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved', 'active'])
            ->exists();

        if ($activeLoan) {
            $this->addError('loanForm.employee_id', 'Cet employé a déjà un prêt en cours.');
            return;
        }

        $monthlyPayment = $this->monthlyPayment;
        $totalAmount = $monthlyPayment * $this->loanForm['installments'];

        EmployeeLoan::create([
            'employee_id' => $this->loanForm['employee_id'],
            'amount' => $this->loanForm['amount'],
            'interest_rate' => $this->loanForm['interest_rate'],
            'total_amount' => $totalAmount,
            'monthly_payment' => $monthlyPayment,
            'installments' => $this->loanForm['installments'],
            'remaining_installments' => $this->loanForm['installments'],
            'remaining_amount' => $totalAmount,
            'reason' => $this->loanForm['reason'],
            'start_date' => $this->loanForm['start_date'],
            'status' => 'pending',
            'requested_by' => auth()->id(),
        ]);

        $this->showCreateModal = false;
        $this->dispatch('notify', type: 'success', message: 'Demande de prêt créée avec succès.');
    }

    public function approveLoan($loanId)
    {
        $loan = EmployeeLoan::findOrFail($loanId);
        $loan->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->dispatch('notify', type: 'success', message: 'Prêt approuvé.');
    }

    public function activateLoan($loanId)
    {
        $loan = EmployeeLoan::findOrFail($loanId);
        $loan->update([
            'status' => 'active',
            'disbursed_at' => now(),
        ]);

        $this->dispatch('notify', type: 'success', message: 'Prêt activé et décaissé.');
    }

    public function rejectLoan($loanId)
    {
        $loan = EmployeeLoan::findOrFail($loanId);
        $loan->update(['status' => 'rejected']);

        $this->dispatch('notify', type: 'success', message: 'Prêt refusé.');
    }

    public function showLoanDetail($loanId)
    {
        $this->selectedLoan = EmployeeLoan::with(['employee', 'payments'])
            ->findOrFail($loanId);
        $this->showDetailModal = true;
    }

    public function render()
    {
        $loans = EmployeeLoan::query()
            ->with(['employee', 'approver'])
            ->when($this->search, function ($q) {
                $q->whereHas('employee', function ($eq) {
                    $eq->where('first_name', 'like', "%{$this->search}%")
                       ->orWhere('last_name', 'like', "%{$this->search}%")
                       ->orWhere('employee_id', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('created_at')
            ->paginate(15);

        $employees = Employee::where('status', 'active')
            ->orderBy('last_name')
            ->get();

        // Stats
        $stats = [
            'total_active' => EmployeeLoan::where('status', 'active')->sum('remaining_amount'),
            'pending_count' => EmployeeLoan::where('status', 'pending')->count(),
            'active_count' => EmployeeLoan::where('status', 'active')->count(),
        ];

        return view('hr::livewire.payroll.employee-loans-list', [
            'loans' => $loans,
            'employees' => $employees,
            'statuses' => EmployeeLoan::STATUSES,
            'stats' => $stats,
        ])->layout('layouts.app');
    }
}
