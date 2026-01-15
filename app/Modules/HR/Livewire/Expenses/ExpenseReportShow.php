<?php

namespace App\Modules\HR\Livewire\Expenses;

use App\Modules\HR\Models\ExpenseReport;
use App\Modules\HR\Services\ExpenseService;
use Livewire\Component;

class ExpenseReportShow extends Component
{
    public ExpenseReport $expenseReport;
    public $showDeleteModal = false;

    public function mount(ExpenseReport $expenseReport)
    {
        $this->expenseReport = $expenseReport->load(['employee', 'lines.category', 'approver']);
    }

    public function submit(ExpenseService $service)
    {
        try {
            $service->submit($this->expenseReport);
            $this->dispatch('notify', message: 'Note de frais soumise avec succès.', type: 'success');
            $this->expenseReport->refresh();
        } catch (\Exception $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function approve(ExpenseService $service)
    {
        if ($service->approve($this->expenseReport, auth()->user())) {
            $this->dispatch('notify', message: 'Note de frais approuvée.', type: 'success');
            $this->expenseReport->refresh();
        }
    }

    public function reject(ExpenseService $service)
    {
        // TODO: Modal pour saisir la raison
        $reason = 'Raison non spécifiée';

        if ($service->reject($this->expenseReport, $reason, auth()->user())) {
            $this->dispatch('notify', message: 'Note de frais rejetée.', type: 'success');
            $this->expenseReport->refresh();
        }
    }

    public function markPaid(ExpenseService $service)
    {
        if ($service->markPaid($this->expenseReport)) {
            $this->dispatch('notify', message: 'Note de frais marquée comme payée.', type: 'success');
            $this->expenseReport->refresh();
        }
    }

    public function confirmDelete()
    {
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
    }

    public function deleteExpenseReport()
    {
        // Vérifier si la note peut être supprimée
        if (!in_array($this->expenseReport->status, ['draft', 'rejected'])) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => "Seules les notes de frais en brouillon ou rejetées peuvent être supprimées."
            ]);
            $this->showDeleteModal = false;
            return;
        }

        $reference = $this->expenseReport->reference;
        $this->expenseReport->lines()->delete();
        $this->expenseReport->delete();

        session()->flash('success', "Note de frais {$reference} supprimée avec succès.");
        $this->js('window.location.href = "' . route('hr.expenses.index') . '"');
    }

    public function render()
    {
        return view('livewire.hr.expenses.expense-report-show')->layout('layouts.app');
    }
}
