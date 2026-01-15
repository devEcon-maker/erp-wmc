<?php

namespace App\Modules\HR\Livewire\Expenses;

use App\Modules\HR\Models\ExpenseReport;
use App\Modules\HR\Models\ExpenseCategory;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Services\ExpenseService;
use Livewire\Component;
use Livewire\WithFileUploads;

class ExpenseReportForm extends Component
{
    use WithFileUploads;

    public ?ExpenseReport $expenseReport = null;

    public $period_start;
    public $period_end;
    public $lines = [];

    // Pour l'ajout de ligne
    public $newLine = [
        'category_id' => '',
        'date' => '',
        'description' => '',
        'amount' => '',
        'receipt' => null,
    ];

    protected $rules = [
        'period_start' => 'required|date',
        'period_end' => 'required|date|after_or_equal:period_start',
        'lines.*.category_id' => 'required|exists:expense_categories,id',
        'lines.*.date' => 'required|date',
        'lines.*.description' => 'required|string|max:255',
        'lines.*.amount' => 'required|numeric|min:0.01',
    ];

    public function mount(ExpenseReport $expenseReport = null)
    {
        if ($expenseReport && $expenseReport->exists) {
            $this->expenseReport = $expenseReport;
            $this->period_start = $expenseReport->period_start->format('Y-m-d');
            $this->period_end = $expenseReport->period_end->format('Y-m-d');

            foreach ($expenseReport->lines as $line) {
                $this->lines[] = [
                    'id' => $line->id,
                    'category_id' => $line->category_id,
                    'date' => $line->date->format('Y-m-d'),
                    'description' => $line->description,
                    'amount' => $line->amount,
                    'receipt_path' => $line->receipt_path,
                    'receipt' => null,
                ];
            }
        } else {
            $this->period_start = now()->startOfMonth()->format('Y-m-d');
            $this->period_end = now()->endOfMonth()->format('Y-m-d');
            $this->newLine['date'] = now()->format('Y-m-d');
        }
    }

    public function addLine()
    {
        $this->validate([
            'newLine.category_id' => 'required|exists:expense_categories,id',
            'newLine.date' => 'required|date',
            'newLine.description' => 'required|string|max:255',
            'newLine.amount' => 'required|numeric|min:0.01',
        ]);

        $category = ExpenseCategory::find($this->newLine['category_id']);

        // Vérifier le plafond
        if ($category->max_amount && $this->newLine['amount'] > $category->max_amount) {
            $this->addError('newLine.amount', "Le montant dépasse le plafond de {$category->max_amount}FCFA pour cette catégorie.");
            return;
        }

        $receiptPath = null;
        if ($this->newLine['receipt']) {
            $receiptPath = $this->newLine['receipt']->store('expenses', 'private');
        }

        // Vérifier si justificatif requis
        if ($category->requires_receipt && !$receiptPath) {
            $this->addError('newLine.receipt', "Un justificatif est requis pour cette catégorie.");
            return;
        }

        $this->lines[] = [
            'id' => null,
            'category_id' => $this->newLine['category_id'],
            'date' => $this->newLine['date'],
            'description' => $this->newLine['description'],
            'amount' => $this->newLine['amount'],
            'receipt_path' => $receiptPath,
            'receipt' => null,
        ];

        // Reset form
        $this->newLine = [
            'category_id' => '',
            'date' => now()->format('Y-m-d'),
            'description' => '',
            'amount' => '',
            'receipt' => null,
        ];
    }

    public function removeLine($index)
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function getTotalProperty(): float
    {
        return collect($this->lines)->sum('amount');
    }

    public function save()
    {
        $this->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        if (count($this->lines) === 0) {
            $this->addError('lines', 'Ajoutez au moins une ligne de dépense.');
            return;
        }

        $employee = Employee::where('user_id', auth()->id())->first();

        if (!$employee) {
            $this->addError('employee', 'Vous devez être associé à un employé pour créer une note de frais.');
            return;
        }

        if ($this->expenseReport && $this->expenseReport->exists) {
            if (!$this->expenseReport->canEdit()) {
                $this->addError('status', 'Cette note de frais ne peut plus être modifiée.');
                return;
            }

            $this->expenseReport->update([
                'period_start' => $this->period_start,
                'period_end' => $this->period_end,
            ]);

            // Supprimer les anciennes lignes et recréer
            $this->expenseReport->lines()->delete();
        } else {
            $this->expenseReport = ExpenseReport::create([
                'employee_id' => $employee->id,
                'period_start' => $this->period_start,
                'period_end' => $this->period_end,
                'status' => 'draft',
            ]);
        }

        foreach ($this->lines as $line) {
            $this->expenseReport->lines()->create([
                'category_id' => $line['category_id'],
                'date' => $line['date'],
                'description' => $line['description'],
                'amount' => $line['amount'],
                'receipt_path' => $line['receipt_path'] ?? null,
            ]);
        }

        // Recalculer le total
        app(ExpenseService::class)->calculateTotal($this->expenseReport);

        return redirect()->route('hr.expenses.show', $this->expenseReport)
            ->with('success', 'Note de frais enregistrée.');
    }

    public function render()
    {
        return view('livewire.hr.expenses.expense-report-form', [
            'categories' => ExpenseCategory::orderBy('name')->get(),
            'total' => $this->total,
        ])->layout('layouts.app');
    }
}
