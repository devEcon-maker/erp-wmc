<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'payment_date',
        'status',
        'working_days',
        'total_gross',
        'total_net',
        'total_employer_charges',
        'validated_by',
        'validated_at',
        'closed_by',
        'closed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'payment_date' => 'date',
        'total_gross' => 'decimal:2',
        'total_net' => 'decimal:2',
        'total_employer_charges' => 'decimal:2',
        'validated_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public const STATUSES = [
        'draft' => 'Brouillon',
        'processing' => 'En cours',
        'validated' => 'Valide',
        'paid' => 'Paye',
        'closed' => 'Cloture',
    ];

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'processing' => 'blue',
            'validated' => 'amber',
            'paid' => 'green',
            'closed' => 'purple',
            default => 'slate',
        };
    }

    public function getEmployeeCountAttribute(): int
    {
        return $this->payslips()->count();
    }

    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'processing']);
    }

    public function canValidate(): bool
    {
        return $this->status === 'processing';
    }

    public function canPay(): bool
    {
        return $this->status === 'validated';
    }

    public function canClose(): bool
    {
        return $this->status === 'paid';
    }

    public function calculateTotals(): void
    {
        $this->total_gross = $this->payslips()->sum('gross_salary');
        $this->total_net = $this->payslips()->sum('net_payable');
        $this->total_employer_charges = $this->payslips()->sum('total_employer_charges');
        $this->save();
    }

    public function validate(User $user): void
    {
        $this->update([
            'status' => 'validated',
            'validated_by' => $user->id,
            'validated_at' => now(),
        ]);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        // Marquer tous les bulletins comme payes
        $this->payslips()->update([
            'status' => 'paid',
            'payment_date' => now(),
        ]);
    }

    public function close(User $user): void
    {
        $this->update([
            'status' => 'closed',
            'closed_by' => $user->id,
            'closed_at' => now(),
        ]);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['draft', 'processing']);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('start_date', $year);
    }

    public static function getCurrent(): ?self
    {
        return static::whereIn('status', ['draft', 'processing'])
            ->orderBy('start_date', 'desc')
            ->first();
    }
}
