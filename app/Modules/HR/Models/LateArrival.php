<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LateArrival extends Model
{
    protected $fillable = [
        'employee_id',
        'attendance_id',
        'date',
        'expected_time',
        'actual_time',
        'minutes_late',
        'reason',
        'justification_path',
        'status',
        'validated_by',
        'validated_at',
        'deduct_from_salary',
    ];

    protected $casts = [
        'date' => 'date',
        'expected_time' => 'datetime:H:i',
        'actual_time' => 'datetime:H:i',
        'validated_at' => 'datetime',
        'deduct_from_salary' => 'boolean',
    ];

    public const STATUSES = [
        'pending' => 'En attente',
        'excused' => 'Excuse',
        'unexcused' => 'Non excuse',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
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
            'pending' => 'amber',
            'excused' => 'green',
            'unexcused' => 'red',
            default => 'gray',
        };
    }

    public function getFormattedExpectedTimeAttribute(): ?string
    {
        return $this->expected_time?->format('H:i');
    }

    public function getFormattedActualTimeAttribute(): ?string
    {
        return $this->actual_time?->format('H:i');
    }

    public function getFormattedLateTimeAttribute(): string
    {
        $hours = floor($this->minutes_late / 60);
        $minutes = $this->minutes_late % 60;

        if ($hours > 0) {
            return sprintf('%dh%02d', $hours, $minutes);
        }
        return sprintf('%d min', $minutes);
    }

    public function getHasJustificationAttribute(): bool
    {
        return !empty($this->justification_path);
    }

    public function getJustificationUrlAttribute(): ?string
    {
        return $this->justification_path ? Storage::url($this->justification_path) : null;
    }

    public function excuse(User $validator): void
    {
        $this->update([
            'status' => 'excused',
            'validated_by' => $validator->id,
            'validated_at' => now(),
            'deduct_from_salary' => false,
        ]);
    }

    public function markUnexcused(User $validator, bool $deductFromSalary = false): void
    {
        $this->update([
            'status' => 'unexcused',
            'validated_by' => $validator->id,
            'validated_at' => now(),
            'deduct_from_salary' => $deductFromSalary,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeExcused($query)
    {
        return $query->where('status', 'excused');
    }

    public function scopeUnexcused($query)
    {
        return $query->where('status', 'unexcused');
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }
}
