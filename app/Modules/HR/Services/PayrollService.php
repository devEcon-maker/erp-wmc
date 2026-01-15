<?php

namespace App\Modules\HR\Services;

use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\PayrollPeriod;
use App\Modules\HR\Models\Payslip;
use App\Modules\HR\Models\PayslipBonus;
use App\Modules\HR\Models\PayslipDeduction;
use App\Modules\HR\Models\DeductionType;
use App\Modules\HR\Models\BonusType;
use App\Modules\HR\Models\EmployeeBonus;
use App\Modules\HR\Models\SalaryAdvance;
use App\Modules\HR\Models\LoanPayment;
use App\Modules\HR\Models\Attendance;
use App\Modules\HR\Models\PublicHoliday;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PayrollService
{
    // Parametres IRPP Cameroun
    private const IRPP_BRACKETS = [
        ['min' => 0, 'max' => 2000000, 'rate' => 10],
        ['min' => 2000000, 'max' => 3000000, 'rate' => 15],
        ['min' => 3000000, 'max' => 5000000, 'rate' => 25],
        ['min' => 5000000, 'max' => PHP_INT_MAX, 'rate' => 35],
    ];

    // Abattement forfaitaire (30% du brut imposable)
    private const TAX_ALLOWANCE_RATE = 0.30;

    /**
     * Creer une nouvelle periode de paie
     */
    public function createPeriod(int $month, int $year): PayrollPeriod
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Verifier si la periode existe deja
        $existing = PayrollPeriod::whereDate('start_date', $startDate->toDateString())
            ->whereDate('end_date', $endDate->toDateString())
            ->first();

        if ($existing) {
            return $existing;
        }

        // Calculer les jours ouvrables
        $workingDays = $this->calculateWorkingDays($startDate, $endDate);

        return PayrollPeriod::create([
            'name' => $startDate->locale('fr')->isoFormat('MMMM YYYY'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'working_days' => $workingDays,
            'status' => 'draft',
        ]);
    }

    /**
     * Calculer les jours ouvrables d'une periode
     */
    public function calculateWorkingDays(Carbon $start, Carbon $end): int
    {
        $workingDays = 0;
        $current = $start->copy();
        $holidays = PublicHoliday::whereBetween('date', [$start, $end])->pluck('date')->toArray();

        while ($current <= $end) {
            // Exclure weekends et jours feries
            if (!$current->isWeekend() && !in_array($current->format('Y-m-d'), $holidays)) {
                $workingDays++;
            }
            $current->addDay();
        }

        return $workingDays;
    }

    /**
     * Generer les bulletins de paie pour une periode
     */
    public function generatePayslips(PayrollPeriod $period): array
    {
        $employees = Employee::active()->get();
        $results = ['success' => 0, 'errors' => []];

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                try {
                    $this->generatePayslip($period, $employee);
                    $results['success']++;
                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'employee' => $employee->full_name,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            $period->update(['status' => 'processing']);
            $period->calculateTotals();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    /**
     * Generer un bulletin de paie pour un employe
     */
    public function generatePayslip(PayrollPeriod $period, Employee $employee): Payslip
    {
        // Verifier si un bulletin existe deja
        $existing = Payslip::where('employee_id', $employee->id)
            ->where('payroll_period_id', $period->id)
            ->first();

        if ($existing) {
            throw new \Exception("Un bulletin existe deja pour cet employe sur cette periode");
        }

        // Verifier que l'employe a un salaire defini
        $baseSalary = $employee->salary ?? 0;
        if ($baseSalary <= 0) {
            throw new \Exception("Le salaire de l'employe {$employee->full_name} n'est pas defini");
        }

        // Calculer les donnees de presence
        $attendanceData = $this->calculateAttendanceData($employee, $period);

        // Creer le bulletin
        $payslip = Payslip::create([
            'employee_id' => $employee->id,
            'payroll_period_id' => $period->id,
            'base_salary' => $baseSalary,
            'worked_days' => $attendanceData['worked_days'],
            'paid_days' => $attendanceData['paid_days'],
            'hourly_rate' => $this->calculateHourlyRate($baseSalary, $period->working_days),
            'regular_hours' => $attendanceData['regular_hours'],
            'overtime_hours_25' => $attendanceData['overtime_25'],
            'overtime_hours_50' => $attendanceData['overtime_50'],
            'overtime_hours_100' => $attendanceData['overtime_100'],
            'gross_salary' => $employee->salary, // Sera recalcule
            'net_salary' => 0,
            'net_payable' => 0,
            'status' => 'draft',
        ]);

        // Ajouter les primes
        $this->addBonuses($payslip, $employee);

        // Ajouter les cotisations et deductions
        $this->addDeductions($payslip);

        // Calculer l'impot
        $this->calculateTax($payslip);

        // Ajouter avances et prets
        $this->addAdvancesAndLoans($payslip, $employee);

        // Recalculer les totaux
        $payslip->calculateTotals();

        return $payslip;
    }

    /**
     * Calculer les donnees de presence
     */
    private function calculateAttendanceData(Employee $employee, PayrollPeriod $period): array
    {
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->get();

        $workedDays = $attendances->where('status', 'present')->count();
        $totalHours = $attendances->sum('worked_hours');
        $overtimeHours = $attendances->sum('overtime_hours');

        // Repartition des heures sup (simplifie)
        $overtime25 = min($overtimeHours, 8 * 4); // 8h par semaine max a 25%
        $overtime50 = max(0, min($overtimeHours - $overtime25, 8 * 4));
        $overtime100 = max(0, $overtimeHours - $overtime25 - $overtime50);

        return [
            'worked_days' => $workedDays ?: $period->working_days,
            'paid_days' => $period->working_days, // Par defaut tous les jours payes
            'regular_hours' => $totalHours - $overtimeHours,
            'overtime_25' => $overtime25,
            'overtime_50' => $overtime50,
            'overtime_100' => $overtime100,
        ];
    }

    /**
     * Calculer le taux horaire
     */
    private function calculateHourlyRate(float $monthlySalary, int $workingDays): float
    {
        $dailyHours = 8;
        $divisor = $workingDays * $dailyHours;
        return $divisor > 0 ? $monthlySalary / $divisor : 0;
    }

    /**
     * Ajouter les primes au bulletin
     */
    private function addBonuses(Payslip $payslip, Employee $employee): void
    {
        // Primes recurrentes de l'employe
        $employeeBonuses = EmployeeBonus::where('employee_id', $employee->id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->with('bonusType')
            ->get();

        foreach ($employeeBonuses as $employeeBonus) {
            PayslipBonus::create([
                'payslip_id' => $payslip->id,
                'bonus_type_id' => $employeeBonus->bonus_type_id,
                'label' => $employeeBonus->bonusType->name,
                'amount' => $employeeBonus->amount,
                'is_taxable' => $employeeBonus->bonusType->is_taxable,
            ]);
        }

        // Prime de transport par defaut
        $transportBonus = BonusType::where('code', 'TRANSPORT')->first();
        if ($transportBonus && !$employeeBonuses->contains('bonus_type_id', $transportBonus->id)) {
            PayslipBonus::create([
                'payslip_id' => $payslip->id,
                'bonus_type_id' => $transportBonus->id,
                'label' => $transportBonus->name,
                'amount' => $transportBonus->default_amount ?? 0,
                'is_taxable' => $transportBonus->is_taxable,
            ]);
        }
    }

    /**
     * Ajouter les cotisations et deductions
     */
    private function addDeductions(Payslip $payslip): void
    {
        $deductionTypes = DeductionType::where('is_active', true)
            ->where('is_mandatory', true)
            ->orderBy('order')
            ->get();

        $grossSalary = $payslip->base_salary + $payslip->bonuses()->where('is_taxable', true)->sum('amount');

        foreach ($deductionTypes as $type) {
            // Determiner la base de calcul
            $base = match ($type->base) {
                'gross_salary' => $grossSalary,
                'base_salary' => $payslip->base_salary,
                default => $grossSalary,
            };

            // Appliquer le plafond
            if ($type->ceiling) {
                $base = min($base, $type->ceiling);
            }

            $employeeAmount = $type->calculateEmployeeAmount($base);
            $employerAmount = $type->calculateEmployerAmount($base);

            if ($employeeAmount > 0 || $employerAmount > 0) {
                PayslipDeduction::create([
                    'payslip_id' => $payslip->id,
                    'deduction_type_id' => $type->id,
                    'label' => $type->name,
                    'base' => $base,
                    'rate' => $type->employee_rate,
                    'employee_amount' => $employeeAmount,
                    'employer_amount' => $employerAmount,
                ]);
            }
        }
    }

    /**
     * Calculer l'impot sur le revenu (IRPP)
     */
    private function calculateTax(Payslip $payslip): void
    {
        // Calculer le brut imposable
        $grossSalary = $payslip->base_salary + $payslip->bonuses()->where('is_taxable', true)->sum('amount');
        $overtimePay = $payslip->calculateOvertimePay();
        $totalGross = $grossSalary + $overtimePay;

        // Cotisations deductibles (CNPS part salariale)
        $socialContributions = $payslip->deductions()
            ->whereHas('deductionType', function ($q) {
                $q->where('code', 'like', 'CNPS%');
            })
            ->sum('employee_amount');

        // Revenu net imposable
        $taxableBase = $totalGross - $socialContributions;

        // Abattement forfaitaire (30%)
        $allowance = $taxableBase * self::TAX_ALLOWANCE_RATE;
        $taxableIncome = $taxableBase - $allowance;

        // Annualiser pour le calcul
        $annualTaxable = $taxableIncome * 12;

        // Calculer l'IRPP par tranches
        $annualTax = $this->calculateProgressiveTax($annualTaxable);

        // Mensualiser
        $monthlyTax = $annualTax / 12;

        // Centimes additionnels (10% de l'IRPP)
        $cac = $monthlyTax * 0.10;

        $payslip->update([
            'taxable_income' => $taxableIncome,
            'income_tax' => $monthlyTax + $cac,
        ]);
    }

    /**
     * Calculer l'impot progressif par tranches
     */
    private function calculateProgressiveTax(float $annualIncome): float
    {
        $tax = 0;

        foreach (self::IRPP_BRACKETS as $bracket) {
            if ($annualIncome <= 0) {
                break;
            }

            $taxableInBracket = min($annualIncome, $bracket['max'] - $bracket['min']);
            $tax += $taxableInBracket * ($bracket['rate'] / 100);
            $annualIncome -= $taxableInBracket;
        }

        return $tax;
    }

    /**
     * Ajouter les avances et prets a deduire
     */
    private function addAdvancesAndLoans(Payslip $payslip, Employee $employee): void
    {
        // Avances a deduire
        $advances = SalaryAdvance::where('employee_id', $employee->id)
            ->where('status', 'paid')
            ->get();

        $totalAdvances = $advances->sum('amount');

        // Echeances de prets du mois
        $loanPayments = LoanPayment::whereHas('loan', function ($q) use ($employee) {
            $q->where('employee_id', $employee->id)->where('status', 'active');
        })
            ->where('status', 'pending')
            ->whereMonth('due_date', $payslip->payrollPeriod->start_date->month)
            ->whereYear('due_date', $payslip->payrollPeriod->start_date->year)
            ->get();

        $totalLoans = $loanPayments->sum('total_amount');

        $payslip->update([
            'advance_deduction' => $totalAdvances,
            'loan_deduction' => $totalLoans,
        ]);
    }

    /**
     * Valider une periode de paie
     */
    public function validatePeriod(PayrollPeriod $period, \App\Models\User $user): void
    {
        if (!$period->canValidate()) {
            throw new \Exception("Cette periode ne peut pas etre validee");
        }

        // Valider tous les bulletins
        $period->payslips()->update([
            'status' => 'validated',
            'validated_by' => $user->id,
            'validated_at' => now(),
        ]);

        $period->validate($user);
    }

    /**
     * Marquer une periode comme payee
     */
    public function markPeriodAsPaid(PayrollPeriod $period, string $paymentMethod): void
    {
        if (!$period->canPay()) {
            throw new \Exception("Cette periode ne peut pas etre marquee comme payee");
        }

        DB::beginTransaction();
        try {
            // Marquer les bulletins comme payes
            foreach ($period->payslips as $payslip) {
                $payslip->markAsPaid($paymentMethod);

                // Marquer les avances comme deduites
                SalaryAdvance::where('employee_id', $payslip->employee_id)
                    ->where('status', 'paid')
                    ->update([
                        'status' => 'deducted',
                        'payslip_id' => $payslip->id,
                    ]);

                // Marquer les echeances de pret
                $employee = $payslip->employee;
                LoanPayment::whereHas('loan', function ($q) use ($employee) {
                    $q->where('employee_id', $employee->id)->where('status', 'active');
                })
                    ->where('status', 'pending')
                    ->whereMonth('due_date', $period->start_date->month)
                    ->whereYear('due_date', $period->start_date->year)
                    ->each(function ($payment) use ($payslip) {
                        $payment->markAsPaid($payslip->id);
                        $payment->loan->recordPayment($payment->total_amount, $payslip->id);
                    });
            }

            $period->markAsPaid();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtenir les statistiques de paie
     */
    public function getStats(?int $year = null): array
    {
        $year = $year ?? now()->year;

        $periods = PayrollPeriod::whereYear('start_date', $year)
            ->where('status', 'closed')
            ->get();

        return [
            'total_gross' => $periods->sum('total_gross'),
            'total_net' => $periods->sum('total_net'),
            'total_employer_charges' => $periods->sum('total_employer_charges'),
            'periods_count' => $periods->count(),
            'average_net' => $periods->count() > 0 ? $periods->sum('total_net') / $periods->count() : 0,
        ];
    }
}
