<?php

namespace App\Modules\HR\Services;

use App\Modules\HR\Models\ExpenseReport;
use App\Modules\HR\Models\ExpenseLine;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    /**
     * Calcule le total d'une note de frais
     */
    public function calculateTotal(ExpenseReport $report): void
    {
        $total = $report->lines()->sum('amount');
        $report->update(['total_amount' => $total]);
    }

    /**
     * Soumet une note de frais pour approbation
     */
    public function submit(ExpenseReport $report): bool
    {
        if (!$report->canSubmit()) {
            return false;
        }

        // Vérifier que toutes les lignes sont valides
        foreach ($report->lines as $line) {
            if ($line->isOverLimit()) {
                throw new \Exception("La ligne '{$line->description}' dépasse le plafond de la catégorie.");
            }
            if ($line->needsReceipt()) {
                throw new \Exception("La ligne '{$line->description}' nécessite un justificatif.");
            }
        }

        $this->calculateTotal($report);

        $report->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        // TODO: Notification au manager/admin

        return true;
    }

    /**
     * Approuve une note de frais
     */
    public function approve(ExpenseReport $report, User $approver): bool
    {
        if (!$report->canApprove()) {
            return false;
        }

        $report->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        // TODO: Notification à l'employé

        return true;
    }

    /**
     * Rejette une note de frais
     */
    public function reject(ExpenseReport $report, string $reason, User $approver): bool
    {
        if (!$report->canApprove()) {
            return false;
        }

        $report->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        // TODO: Notification à l'employé

        return true;
    }

    /**
     * Marque une note de frais comme payée
     */
    public function markPaid(ExpenseReport $report): bool
    {
        if (!$report->canPay()) {
            return false;
        }

        $report->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // TODO: Notification à l'employé

        return true;
    }

    /**
     * Ajoute une ligne à une note de frais
     */
    public function addLine(ExpenseReport $report, array $data): ExpenseLine
    {
        if (!$report->canEdit()) {
            throw new \Exception("Cette note de frais ne peut plus être modifiée.");
        }

        $line = $report->lines()->create($data);
        $this->calculateTotal($report);

        return $line;
    }

    /**
     * Supprime une ligne d'une note de frais
     */
    public function removeLine(ExpenseLine $line): bool
    {
        $report = $line->expenseReport;

        if (!$report->canEdit()) {
            throw new \Exception("Cette note de frais ne peut plus être modifiée.");
        }

        $line->delete();
        $this->calculateTotal($report);

        return true;
    }

    /**
     * Récupère les notes de frais en attente d'approbation
     */
    public function getPendingApproval()
    {
        return ExpenseReport::with(['employee', 'lines.category'])
            ->where('status', 'submitted')
            ->orderBy('submitted_at')
            ->get();
    }

    /**
     * Statistiques des notes de frais
     */
    public function getStats(?int $employeeId = null): array
    {
        $query = ExpenseReport::query();

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        return [
            'total_draft' => (clone $query)->draft()->count(),
            'total_submitted' => (clone $query)->submitted()->count(),
            'total_approved' => (clone $query)->approved()->count(),
            'total_paid' => (clone $query)->where('status', 'paid')->count(),
            'amount_pending' => (clone $query)->whereIn('status', ['submitted', 'approved'])->sum('total_amount'),
            'amount_paid_this_month' => (clone $query)->where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('total_amount'),
        ];
    }
}
