<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PermissionRequest extends Model
{
    protected $table = 'permissions_requests';

    protected $fillable = [
        'employee_id',
        'type',
        'date',
        'start_time',
        'end_time',
        'hours',
        'reason',
        'justification_path',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'deduct_from_salary',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'hours' => 'decimal:2',
        'approved_at' => 'datetime',
        'deduct_from_salary' => 'boolean',
    ];

    public const TYPES = [
        'personal' => 'Personnel',
        'medical' => 'Medical',
        'administrative' => 'Administratif',
        'family' => 'Familial',
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

    public function getFormattedTimeRangeAttribute(): string
    {
        $start = $this->start_time?->format('H:i') ?? '--:--';
        $end = $this->end_time?->format('H:i') ?? '--:--';
        return "{$start} - {$end}";
    }

    public function getFormattedHoursAttribute(): string
    {
        $hours = floor($this->hours);
        $minutes = round(($this->hours - $hours) * 60);
        return sprintf('%dh%02d', $hours, $minutes);
    }

    public function getHasJustificationAttribute(): bool
    {
        return !empty($this->justification_path);
    }

    public function getJustificationUrlAttribute(): ?string
    {
        return $this->justification_path ? Storage::url($this->justification_path) : null;
    }

    public function calculateHours(): float
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }
        return round($this->start_time->diffInMinutes($this->end_time) / 60, 2);
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

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }
}
