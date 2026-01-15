<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanPayment extends Model
{
    protected $fillable = [
        'employee_loan_id',
        'payslip_id',
        'payment_number',
        'due_date',
        'principal_amount',
        'interest_amount',
        'total_amount',
        'status',
        'paid_date',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public const STATUSES = [
        'pending' => 'En attente',
        'paid' => 'Paye',
        'overdue' => 'En retard',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(EmployeeLoan::class, 'employee_loan_id');
    }

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'amber',
            'paid' => 'green',
            'overdue' => 'red',
            default => 'gray',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }

    public function markAsPaid(int $payslipId): void
    {
        $this->update([
            'status' => 'paid',
            'paid_date' => now(),
            'payslip_id' => $payslipId,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
            ->where('due_date', '<', now());
    }

    public function scopeDueThisMonth($query)
    {
        return $query->where('status', 'pending')
            ->whereYear('due_date', now()->year)
            ->whereMonth('due_date', now()->month);
    }
}
