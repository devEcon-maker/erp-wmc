<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'break_start',
        'break_end',
        'worked_hours',
        'overtime_hours',
        'status',
        'is_late',
        'late_minutes',
        'left_early',
        'early_departure_minutes',
        'notes',
        'check_in_location',
        'check_out_location',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime:H:i',
        'check_out' => 'datetime:H:i',
        'break_start' => 'datetime:H:i',
        'break_end' => 'datetime:H:i',
        'worked_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'is_late' => 'boolean',
        'left_early' => 'boolean',
        'validated_at' => 'datetime',
    ];

    public const STATUSES = [
        'present' => 'Present',
        'absent' => 'Absent',
        'late' => 'En retard',
        'half_day' => 'Demi-journee',
        'holiday' => 'Jour ferie',
        'weekend' => 'Week-end',
        'leave' => 'Conge',
        'remote' => 'Teletravail',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function lateArrival(): HasOne
    {
        return $this->hasOne(LateArrival::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'present' => 'green',
            'absent' => 'red',
            'late' => 'amber',
            'half_day' => 'blue',
            'holiday', 'weekend' => 'purple',
            'leave' => 'cyan',
            'remote' => 'indigo',
            default => 'gray',
        };
    }

    public function getFormattedCheckInAttribute(): ?string
    {
        return $this->check_in?->format('H:i');
    }

    public function getFormattedCheckOutAttribute(): ?string
    {
        return $this->check_out?->format('H:i');
    }

    public function getFormattedWorkedHoursAttribute(): string
    {
        $hours = floor($this->worked_hours ?? 0);
        $minutes = round(($this->worked_hours - $hours) * 60);
        return sprintf('%dh%02d', $hours, $minutes);
    }

    public function calculateWorkedHours(): float
    {
        if (!$this->check_in || !$this->check_out) {
            return 0;
        }

        $totalMinutes = $this->check_in->diffInMinutes($this->check_out);

        // Deduire la pause si renseignee
        if ($this->break_start && $this->break_end) {
            $breakMinutes = $this->break_start->diffInMinutes($this->break_end);
            $totalMinutes -= $breakMinutes;
        }

        return round($totalMinutes / 60, 2);
    }

    public function checkIn(string $time, ?string $location = null): void
    {
        $this->update([
            'check_in' => $time,
            'check_in_location' => $location,
            'status' => 'present',
        ]);
    }

    public function checkOut(string $time, ?string $location = null): void
    {
        $this->update([
            'check_out' => $time,
            'check_out_location' => $location,
            'worked_hours' => $this->calculateWorkedHours(),
        ]);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }
}
