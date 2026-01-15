<?php

namespace App\Modules\HR\Livewire\Payroll;

use App\Modules\HR\Models\SalaryAdvance;
use App\Modules\HR\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;

class SalaryAdvancesList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $monthFilter = '';

    // Modal création
    public $showCreateModal = false;
    public $advanceForm = [
        'employee_id' => '',
        'amount' => '',
        'reason' => '',
        'repayment_month' => '',
    ];

    // Modal approbation
    public $showApprovalModal = false;
    public $selectedAdvance = null;
    public $approvalNotes = '';

    protected $queryString = ['search', 'statusFilter'];

    public function openCreateModal()
    {
        $this->reset('advanceForm');
        $this->advanceForm['repayment_month'] = date('Y-m', strtotime('+1 month'));
        $this->showCreateModal = true;
    }

    public function createAdvance()
    {
        $this->validate([
            'advanceForm.employee_id' => 'required|exists:employees,id',
            'advanceForm.amount' => 'required|numeric|min:1000',
            'advanceForm.reason' => 'nullable|string|max:500',
            'advanceForm.repayment_month' => 'required|date_format:Y-m',
        ]);

        $employee = Employee::findOrFail($this->advanceForm['employee_id']);

        // Vérifier que l'avance ne dépasse pas 50% du salaire
        $maxAdvance = $employee->base_salary * 0.5;
        if ($this->advanceForm['amount'] > $maxAdvance) {
            $this->addError('advanceForm.amount', "L'avance ne peut pas dépasser 50% du salaire ({$maxAdvance} FCFA).");
            return;
        }

        // Vérifier qu'il n'y a pas d'avance en attente
        $pendingAdvance = SalaryAdvance::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('is_repaid', false)
            ->exists();

        if ($pendingAdvance) {
            $this->addError('advanceForm.employee_id', 'Cet employé a déjà une avance en cours.');
            return;
        }

        SalaryAdvance::create([
            'employee_id' => $this->advanceForm['employee_id'],
            'amount' => $this->advanceForm['amount'],
            'reason' => $this->advanceForm['reason'],
            'request_date' => now(),
            'repayment_month' => $this->advanceForm['repayment_month'] . '-01',
            'status' => 'pending',
            'requested_by' => auth()->id(),
        ]);

        $this->showCreateModal = false;
        $this->dispatch('notify', type: 'success', message: 'Demande d\'avance créée avec succès.');
    }

    public function openApprovalModal($advanceId)
    {
        $this->selectedAdvance = SalaryAdvance::with('employee')->findOrFail($advanceId);
        $this->approvalNotes = '';
        $this->showApprovalModal = true;
    }

    public function approveAdvance()
    {
        $this->selectedAdvance->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_notes' => $this->approvalNotes,
        ]);

        $this->showApprovalModal = false;
        $this->dispatch('notify', type: 'success', message: 'Avance approuvée.');
    }

    public function rejectAdvance()
    {
        $this->validate(['approvalNotes' => 'required|string|min:10']);

        $this->selectedAdvance->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_notes' => $this->approvalNotes,
        ]);

        $this->showApprovalModal = false;
        $this->dispatch('notify', type: 'success', message: 'Avance refusée.');
    }

    public function markAsPaid($advanceId)
    {
        $advance = SalaryAdvance::findOrFail($advanceId);
        $advance->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->dispatch('notify', type: 'success', message: 'Avance marquée comme payée.');
    }

    public function render()
    {
        $advances = SalaryAdvance::query()
            ->with(['employee', 'approver'])
            ->when($this->search, function ($q) {
                $q->whereHas('employee', function ($eq) {
                    $eq->where('first_name', 'like', "%{$this->search}%")
                       ->orWhere('last_name', 'like', "%{$this->search}%")
                       ->orWhere('employee_id', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->monthFilter, fn($q) => $q->whereMonth('request_date', substr($this->monthFilter, 5, 2))
                ->whereYear('request_date', substr($this->monthFilter, 0, 4)))
            ->orderByDesc('request_date')
            ->paginate(15);

        $employees = Employee::where('status', 'active')
            ->orderBy('last_name')
            ->get();

        return view('hr::livewire.payroll.salary-advances-list', [
            'advances' => $advances,
            'employees' => $employees,
            'statuses' => SalaryAdvance::STATUSES,
        ])->layout('layouts.app');
    }
}
