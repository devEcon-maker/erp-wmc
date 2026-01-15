<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkSchedule extends Model
{
    protected $fillable = [
        'name',
        'is_default',
        'monday_start',
        'monday_end',
        'tuesday_start',
        'tuesday_end',
        'wednesday_start',
        'wednesday_end',
        'thursday_start',
        'thursday_end',
        'friday_start',
        'friday_end',
        'saturday_start',
        'saturday_end',
        'sunday_start',
        'sunday_end',
        'break_start',
        'break_end',
        'late_tolerance_minutes',
        'daily_hours',
        'weekly_hours',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'daily_hours' => 'decimal:2',
        'weekly_hours' => 'decimal:2',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function getScheduleForDay(string $day): ?array
    {
        $dayLower = strtolower($day);
        $startKey = "{$dayLower}_start";
        $endKey = "{$dayLower}_end";

        if (!$this->$startKey || !$this->$endKey) {
            return null;
        }

        return [
            'start' => $this->$startKey,
            'end' => $this->$endKey,
        ];
    }

    public function isWorkingDay(string $day): bool
    {
        return $this->getScheduleForDay($day) !== null;
    }

    public function getExpectedStartTime(\DateTimeInterface $date): ?string
    {
        $dayName = strtolower($date->format('l'));
        $startKey = "{$dayName}_start";
        return $this->$startKey;
    }

    public function getExpectedEndTime(\DateTimeInterface $date): ?string
    {
        $dayName = strtolower($date->format('l'));
        $endKey = "{$dayName}_end";
        return $this->$endKey;
    }

    public function getWorkingDaysPerWeekAttribute(): int
    {
        $count = 0;
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($days as $day) {
            if ($this->isWorkingDay($day)) {
                $count++;
            }
        }

        return $count;
    }

    public function getBreakDurationMinutesAttribute(): int
    {
        if (!$this->break_start || !$this->break_end) {
            return 0;
        }

        $start = \Carbon\Carbon::parse($this->break_start);
        $end = \Carbon\Carbon::parse($this->break_end);

        return $start->diffInMinutes($end);
    }

    public function calculateLateMinutes(string $checkInTime, \DateTimeInterface $date): int
    {
        $expectedStart = $this->getExpectedStartTime($date);

        if (!$expectedStart) {
            return 0;
        }

        $expected = \Carbon\Carbon::parse($expectedStart);
        $actual = \Carbon\Carbon::parse($checkInTime);

        $diff = $expected->diffInMinutes($actual, false);

        // Appliquer la tolerance
        $diff -= $this->late_tolerance_minutes;

        return max(0, $diff);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }
}
