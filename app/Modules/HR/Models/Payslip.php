<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payslip extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'payroll_period_id',
        'reference',
        'base_salary',
        'worked_days',
        'paid_days',
        'hourly_rate',
        'regular_hours',
        'overtime_hours_25',
        'overtime_hours_50',
        'overtime_hours_100',
        'night_hours',
        'holiday_hours',
        'gross_salary',
        'total_bonuses',
        'total_deductions',
        'total_employee_charges',
        'total_employer_charges',
        'taxable_income',
        'income_tax',
        'net_salary',
        'net_payable',
        'advance_deduction',
        'loan_deduction',
        'status',
        'payment_date',
        'payment_method',
        'payment_reference',
        'notes',
        'created_by',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'hourly_rate' => 'decimal:4',
        'regular_hours' => 'decimal:2',
        'overtime_hours_25' => 'decimal:2',
        'overtime_hours_50' => 'decimal:2',
        'overtime_hours_100' => 'decimal:2',
        'night_hours' => 'decimal:2',
        'holiday_hours' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'total_bonuses' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_employee_charges' => 'decimal:2',
        'total_employer_charges' => 'decimal:2',
        'taxable_income' => 'decimal:2',
        'income_tax' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'loan_deduction' => 'decimal:2',
        'payment_date' => 'date',
        'validated_at' => 'datetime',
    ];

    public const STATUSES = [
        'draft' => 'Brouillon',
        'validated' => 'Valide',
        'paid' => 'Paye',
    ];

    public const PAYMENT_METHODS = [
        'bank_transfer' => 'Virement bancaire',
        'cash' => 'Especes',
        'check' => 'Cheque',
        'mobile_money' => 'Mobile Money',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payslip) {
            if (empty($payslip->reference)) {
                $period = PayrollPeriod::find($payslip->payroll_period_id);
                $yearMonth = $period ? $period->start_date->format('Y-m') : now()->format('Y-m');
                $lastPayslip = static::withTrashed()
                    ->where('reference', 'like', "BP-{$yearMonth}-%")
                    ->orderBy('id', 'desc')
                    ->first();
                $lastNum = $lastPayslip ? (int) substr($lastPayslip->reference, -4) : 0;
                $payslip->reference = 'BP-' . $yearMonth . '-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function bonuses(): HasMany
    {
        return $this->hasMany(PayslipBonus::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(PayslipDeduction::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'validated' => 'amber',
            'paid' => 'green',
            default => 'slate',
        };
    }

    public function getPaymentMethodLabelAttribute(): ?string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public function getTotalOvertimeHoursAttribute(): float
    {
        return $this->overtime_hours_25 + $this->overtime_hours_50 + $this->overtime_hours_100;
    }

    public function canEdit(): bool
    {
        return $this->status === 'draft';
    }

    public function canValidate(): bool
    {
        return $this->status === 'draft';
    }

    public function validate(User $user): void
    {
        $this->update([
            'status' => 'validated',
            'validated_by' => $user->id,
            'validated_at' => now(),
        ]);
    }

    public function markAsPaid(string $method, ?string $reference = null): void
    {
        $this->update([
            'status' => 'paid',
            'payment_date' => now(),
            'payment_method' => $method,
            'payment_reference' => $reference,
        ]);
    }

    public function calculateTotals(): void
    {
        $this->total_bonuses = $this->bonuses()->sum('amount');
        $this->total_employee_charges = $this->deductions()->sum('employee_amount');
        $this->total_employer_charges = $this->deductions()->sum('employer_amount');

        // Calcul du brut
        $this->gross_salary = $this->base_salary + $this->total_bonuses + $this->calculateOvertimePay();

        // Calcul du net
        $this->net_salary = $this->gross_salary - $this->total_employee_charges - $this->income_tax;

        // Net a payer (apres avances et prets)
        $this->net_payable = $this->net_salary - $this->advance_deduction - $this->loan_deduction;

        $this->save();
    }

    public function calculateOvertimePay(): float
    {
        $hourlyRate = $this->hourly_rate ?? ($this->base_salary / ($this->paid_days * 8));

        $overtime25 = $this->overtime_hours_25 * $hourlyRate * 1.25;
        $overtime50 = $this->overtime_hours_50 * $hourlyRate * 1.50;
        $overtime100 = $this->overtime_hours_100 * $hourlyRate * 2.00;

        return $overtime25 + $overtime50 + $overtime100;
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeValidated($query)
    {
        return $query->where('status', 'validated');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForPeriod($query, int $periodId)
    {
        return $query->where('payroll_period_id', $periodId);
    }
}
