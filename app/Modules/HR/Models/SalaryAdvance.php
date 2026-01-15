<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryAdvance extends Model
{
    protected $fillable = [
        'employee_id',
        'reference',
        'amount',
        'request_date',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'payment_date',
        'payment_method',
        'payslip_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'request_date' => 'date',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public const STATUSES = [
        'pending' => 'En attente',
        'approved' => 'Approuvee',
        'rejected' => 'Rejetee',
        'paid' => 'Payee',
        'deducted' => 'Deduite',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($advance) {
            if (empty($advance->reference)) {
                $year = now()->format('Y');
                $lastAdvance = static::where('reference', 'like', "AV-{$year}-%")
                    ->orderBy('id', 'desc')
                    ->first();
                $lastNum = $lastAdvance ? (int) substr($lastAdvance->reference, -4) : 0;
                $advance->reference = 'AV-' . $year . '-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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
            'approved' => 'blue',
            'rejected' => 'red',
            'paid' => 'cyan',
            'deducted' => 'green',
            default => 'gray',
        };
    }

    public function canApprove(): bool
    {
        return $this->status === 'pending';
    }

    public function canPay(): bool
    {
        return $this->status === 'approved';
    }

    public function approve(User $approver): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }

    public function reject(User $approver, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function markAsPaid(string $method): void
    {
        $this->update([
            'status' => 'paid',
            'payment_date' => now(),
            'payment_method' => $method,
        ]);
    }

    public function markAsDeducted(int $payslipId): void
    {
        $this->update([
            'status' => 'deducted',
            'payslip_id' => $payslipId,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePendingDeduction($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }
}
