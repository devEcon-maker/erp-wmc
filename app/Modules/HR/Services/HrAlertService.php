<?php

namespace App\Modules\HR\Services;

use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\EmployeeDocument;
use App\Modules\HR\Models\EmployeeContract;
use App\Modules\HR\Models\HrAlert;
use App\Modules\HR\Models\LoanPayment;
use App\Modules\HR\Models\LeaveBalance;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class HrAlertService
{
    /**
     * Generer toutes les alertes automatiques
     */
    public function generateAlerts(): array
    {
        $results = [
            'contract_expiry' => $this->generateContractExpiryAlerts(),
            'probation_end' => $this->generateProbationEndAlerts(),
            'document_expiry' => $this->generateDocumentExpiryAlerts(),
            'birthday' => $this->generateBirthdayAlerts(),
            'work_anniversary' => $this->generateWorkAnniversaryAlerts(),
            'loan_payment' => $this->generateLoanPaymentAlerts(),
            'leave_balance' => $this->generateLeaveBalanceAlerts(),
        ];

        return $results;
    }

    /**
     * Alertes d'expiration de contrat
     */
    public function generateContractExpiryAlerts(int $daysAhead = 30): int
    {
        $employees = Employee::active()
            ->whereNotNull('contract_end_date')
            ->where('contract_end_date', '>=', now())
            ->where('contract_end_date', '<=', now()->addDays($daysAhead))
            ->get();

        $count = 0;
        foreach ($employees as $employee) {
            $daysRemaining = now()->diffInDays($employee->contract_end_date);
            $priority = match (true) {
                $daysRemaining <= 7 => 'critical',
                $daysRemaining <= 14 => 'high',
                default => 'medium',
            };

            $existing = HrAlert::where('employee_id', $employee->id)
                ->where('type', 'contract_expiry')
                ->where('status', 'pending')
                ->first();

            if (!$existing) {
                HrAlert::create([
                    'employee_id' => $employee->id,
                    'type' => 'contract_expiry',
                    'title' => "Contrat expire dans {$daysRemaining} jours",
                    'description' => "Le contrat de {$employee->full_name} ({$employee->contract_type_label}) expire le {$employee->contract_end_date->format('d/m/Y')}.",
                    'alert_date' => now(),
                    'due_date' => $employee->contract_end_date,
                    'priority' => $priority,
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Alertes de fin de periode d'essai
     */
    public function generateProbationEndAlerts(int $daysAhead = 14): int
    {
        $employees = Employee::active()
            ->where('probation_completed', false)
            ->whereNotNull('probation_end_date')
            ->where('probation_end_date', '>=', now())
            ->where('probation_end_date', '<=', now()->addDays($daysAhead))
            ->get();

        $count = 0;
        foreach ($employees as $employee) {
            $daysRemaining = now()->diffInDays($employee->probation_end_date);

            $existing = HrAlert::where('employee_id', $employee->id)
                ->where('type', 'probation_end')
                ->where('status', 'pending')
                ->first();

            if (!$existing) {
                HrAlert::create([
                    'employee_id' => $employee->id,
                    'type' => 'probation_end',
                    'title' => "Fin periode d'essai dans {$daysRemaining} jours",
                    'description' => "La periode d'essai de {$employee->full_name} se termine le {$employee->probation_end_date->format('d/m/Y')}. Une evaluation est requise.",
                    'alert_date' => now(),
                    'due_date' => $employee->probation_end_date,
                    'priority' => $daysRemaining <= 7 ? 'high' : 'medium',
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Alertes d'expiration de documents
     */
    public function generateDocumentExpiryAlerts(int $daysAhead = 30): int
    {
        $documents = EmployeeDocument::whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays($daysAhead))
            ->with('employee')
            ->get();

        $count = 0;
        foreach ($documents as $document) {
            if (!$document->employee) {
                continue;
            }

            $daysRemaining = now()->diffInDays($document->expiry_date);

            $existing = HrAlert::where('employee_id', $document->employee_id)
                ->where('type', 'document_expiry')
                ->where('title', 'like', "%{$document->type_label}%")
                ->where('status', 'pending')
                ->first();

            if (!$existing) {
                HrAlert::create([
                    'employee_id' => $document->employee_id,
                    'type' => 'document_expiry',
                    'title' => "{$document->type_label} expire dans {$daysRemaining} jours",
                    'description' => "Le document '{$document->name}' de {$document->employee->full_name} expire le {$document->expiry_date->format('d/m/Y')}.",
                    'alert_date' => now(),
                    'due_date' => $document->expiry_date,
                    'priority' => $daysRemaining <= 7 ? 'high' : 'medium',
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Alertes d'anniversaire
     */
    public function generateBirthdayAlerts(int $daysAhead = 7): int
    {
        $today = now();
        $endDate = now()->addDays($daysAhead);

        $employees = Employee::active()
            ->whereNotNull('birth_date')
            ->get()
            ->filter(function ($employee) use ($today, $endDate) {
                $birthday = $employee->birth_date->setYear($today->year);
                if ($birthday->lt($today)) {
                    $birthday->addYear();
                }
                return $birthday->between($today, $endDate);
            });

        $count = 0;
        foreach ($employees as $employee) {
            $birthday = $employee->birth_date->setYear($today->year);
            if ($birthday->lt($today)) {
                $birthday->addYear();
            }

            $daysRemaining = $today->diffInDays($birthday);
            $age = $birthday->year - $employee->birth_date->year;

            $existing = HrAlert::where('employee_id', $employee->id)
                ->where('type', 'birthday')
                ->whereYear('due_date', $birthday->year)
                ->first();

            if (!$existing) {
                HrAlert::create([
                    'employee_id' => $employee->id,
                    'type' => 'birthday',
                    'title' => $daysRemaining === 0 ? "Anniversaire aujourd'hui!" : "Anniversaire dans {$daysRemaining} jours",
                    'description' => "{$employee->full_name} aura {$age} ans le {$birthday->format('d/m/Y')}.",
                    'alert_date' => now(),
                    'due_date' => $birthday,
                    'priority' => 'low',
                    'is_recurring' => true,
                    'recurrence_type' => 'yearly',
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Alertes d'anniversaire de travail
     */
    public function generateWorkAnniversaryAlerts(int $daysAhead = 7): int
    {
        $today = now();
        $endDate = now()->addDays($daysAhead);

        $employees = Employee::active()
            ->whereNotNull('hire_date')
            ->get()
            ->filter(function ($employee) use ($today, $endDate) {
                $anniversary = $employee->hire_date->setYear($today->year);
                if ($anniversary->lt($today)) {
                    $anniversary->addYear();
                }
                return $anniversary->between($today, $endDate);
            });

        $count = 0;
        foreach ($employees as $employee) {
            $anniversary = $employee->hire_date->setYear($today->year);
            if ($anniversary->lt($today)) {
                $anniversary->addYear();
            }

            $daysRemaining = $today->diffInDays($anniversary);
            $years = $anniversary->year - $employee->hire_date->year;

            // Ne pas creer d'alerte pour moins d'un an
            if ($years < 1) {
                continue;
            }

            $existing = HrAlert::where('employee_id', $employee->id)
                ->where('type', 'work_anniversary')
                ->whereYear('due_date', $anniversary->year)
                ->first();

            if (!$existing) {
                HrAlert::create([
                    'employee_id' => $employee->id,
                    'type' => 'work_anniversary',
                    'title' => $daysRemaining === 0 ? "{$years} ans d'anciennete!" : "Anniversaire de travail dans {$daysRemaining} jours",
                    'description' => "{$employee->full_name} celebrera {$years} ans dans l'entreprise le {$anniversary->format('d/m/Y')}.",
                    'alert_date' => now(),
                    'due_date' => $anniversary,
                    'priority' => 'low',
                    'is_recurring' => true,
                    'recurrence_type' => 'yearly',
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Alertes d'echeances de prets
     */
    public function generateLoanPaymentAlerts(): int
    {
        $payments = LoanPayment::where('status', 'pending')
            ->where('due_date', '<=', now()->addDays(7))
            ->with(['loan.employee'])
            ->get();

        $count = 0;
        foreach ($payments as $payment) {
            if (!$payment->loan || !$payment->loan->employee) {
                continue;
            }

            $employee = $payment->loan->employee;
            $daysRemaining = now()->diffInDays($payment->due_date, false);
            $isOverdue = $daysRemaining < 0;

            $existing = HrAlert::where('employee_id', $employee->id)
                ->where('type', 'loan_payment_due')
                ->where('due_date', $payment->due_date)
                ->where('status', 'pending')
                ->first();

            if (!$existing) {
                HrAlert::create([
                    'employee_id' => $employee->id,
                    'type' => 'loan_payment_due',
                    'title' => $isOverdue
                        ? "Echeance de pret en retard"
                        : "Echeance de pret dans {$daysRemaining} jours",
                    'description' => "Echeance {$payment->payment_number} du pret {$payment->loan->reference} - Montant: " . number_format($payment->total_amount, 0, ',', ' ') . " FCFA",
                    'alert_date' => now(),
                    'due_date' => $payment->due_date,
                    'priority' => $isOverdue ? 'critical' : ($daysRemaining <= 3 ? 'high' : 'medium'),
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Alertes de solde de conges faible
     */
    public function generateLeaveBalanceAlerts(int $threshold = 3): int
    {
        $balances = LeaveBalance::where('year', now()->year)
            ->whereColumn('allocated', '>', 'used')
            ->whereRaw('(allocated - used) <= ?', [$threshold])
            ->with(['employee', 'leaveType'])
            ->get();

        $count = 0;
        foreach ($balances as $balance) {
            if (!$balance->employee || !$balance->leaveType) {
                continue;
            }

            $remaining = $balance->allocated - $balance->used;

            $existing = HrAlert::where('employee_id', $balance->employee_id)
                ->where('type', 'leave_balance_low')
                ->where('status', 'pending')
                ->whereYear('created_at', now()->year)
                ->first();

            if (!$existing) {
                HrAlert::create([
                    'employee_id' => $balance->employee_id,
                    'type' => 'leave_balance_low',
                    'title' => "Solde de conges faible ({$remaining} jours)",
                    'description' => "{$balance->employee->full_name} n'a plus que {$remaining} jours de {$balance->leaveType->name}.",
                    'alert_date' => now(),
                    'priority' => $remaining <= 1 ? 'high' : 'medium',
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Obtenir les alertes actives
     */
    public function getActiveAlerts(?int $userId = null): Collection
    {
        $query = HrAlert::active()
            ->with('employee')
            ->orderBy('priority', 'desc')
            ->orderBy('due_date');

        if ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->where('assigned_to', $userId)
                    ->orWhereNull('assigned_to');
            });
        }

        return $query->get();
    }

    /**
     * Obtenir les alertes par type
     */
    public function getAlertsByType(string $type): Collection
    {
        return HrAlert::where('type', $type)
            ->active()
            ->with('employee')
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Marquer une alerte comme resolue
     */
    public function resolveAlert(HrAlert $alert, \App\Models\User $user, ?string $notes = null): void
    {
        $alert->resolve($user, $notes);
    }

    /**
     * Obtenir le resume des alertes
     */
    public function getAlertsSummary(): array
    {
        $alerts = HrAlert::active()->get();

        return [
            'total' => $alerts->count(),
            'critical' => $alerts->where('priority', 'critical')->count(),
            'high' => $alerts->where('priority', 'high')->count(),
            'medium' => $alerts->where('priority', 'medium')->count(),
            'low' => $alerts->where('priority', 'low')->count(),
            'overdue' => $alerts->where('is_overdue', true)->count(),
            'by_type' => $alerts->groupBy('type')->map->count(),
        ];
    }
}
