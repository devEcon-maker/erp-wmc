<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Absence extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'start_date',
        'end_date',
        'days_count',
        'reason',
        'justification_path',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'deduct_from_salary',
        'deduct_from_leave',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days_count' => 'decimal:2',
        'approved_at' => 'datetime',
        'deduct_from_salary' => 'boolean',
        'deduct_from_leave' => 'boolean',
    ];

    public const TYPES = [
        'unexcused' => 'Absence non justifiee',
        'excused' => 'Absence justifiee',
        'sick' => 'Maladie',
        'family_emergency' => 'Urgence familiale',
        'bereavement' => 'Deces',
        'other' => 'Autre',
    ];

    public const STATUSES = [
        'pending' => 'En attente',
        'approved' => 'Approuvee',
        'rejected' => 'Rejetee',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'amber',
            'approved' => 'green',
            'rejected' => 'red',
            default => 'gray',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'unexcused' => 'red',
            'excused' => 'green',
            'sick' => 'amber',
            'family_emergency', 'bereavement' => 'purple',
            default => 'gray',
        };
    }

    public function getHasJustificationAttribute(): bool
    {
        return !empty($this->justification_path);
    }

    public function getJustificationUrlAttribute(): ?string
    {
        return $this->justification_path ? Storage::url($this->justification_path) : null;
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

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($q2) use ($startDate, $endDate) {
                    $q2->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        });
    }
}
