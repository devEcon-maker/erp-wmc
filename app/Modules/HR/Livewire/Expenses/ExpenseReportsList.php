<?php

namespace App\Modules\HR\Livewire\Expenses;

use App\Modules\HR\Models\ExpenseReport;
use App\Modules\HR\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseReportsList extends Component
{
    use WithPagination;

    public $status = '';
    public $employeeId = '';
    public $search = '';
    public $showPendingApproval = false;

    protected $queryString = [
        'status' => ['except' => ''],
        'employeeId' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function getReportsProperty()
    {
        $query = ExpenseReport::with(['employee', 'lines.category', 'approver']);

        // Si on affiche les demandes en attente d'approbation (pour manager/admin)
        if ($this->showPendingApproval) {
            $query->where('status', 'submitted');
        } else {
            // Sinon, montrer les notes de l'utilisateur connecté
            $currentEmployee = Employee::where('user_id', auth()->id())->first();
            if ($currentEmployee && !auth()->user()->can('expenses.approve')) {
                $query->where('employee_id', $currentEmployee->id);
            }
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }

        if ($this->search) {
            $query->where('reference', 'like', '%' . $this->search . '%');
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    public function getStatsProperty(): array
    {
        $currentEmployee = Employee::where('user_id', auth()->id())->first();

        return [
            'draft' => ExpenseReport::when($currentEmployee, fn($q) => $q->where('employee_id', $currentEmployee->id))->draft()->count(),
            'submitted' => ExpenseReport::when($currentEmployee, fn($q) => $q->where('employee_id', $currentEmployee->id))->submitted()->count(),
            'approved' => ExpenseReport::when($currentEmployee, fn($q) => $q->where('employee_id', $currentEmployee->id))->approved()->count(),
            'pending_approval' => ExpenseReport::where('status', 'submitted')->count(),
        ];
    }

    public function togglePendingApproval()
    {
        $this->showPendingApproval = !$this->showPendingApproval;
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.hr.expenses.expense-reports-list', [
            'reports' => $this->reports,
            'stats' => $this->stats,
            'employees' => Employee::orderBy('last_name')->get(),
        ])->layout('layouts.app');
    }
}
