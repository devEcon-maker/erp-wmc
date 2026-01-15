<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;

class PublicHoliday extends Model
{
    protected $fillable = [
        'name',
        'date',
        'year',
        'is_recurring',
        'is_paid',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
        'is_paid' => 'boolean',
    ];

    public static function isHoliday(\DateTimeInterface $date): bool
    {
        return static::where('date', $date->format('Y-m-d'))->exists();
    }

    public static function getHolidaysForYear(int $year): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('year', $year)->orderBy('date')->get();
    }

    public static function getHolidaysBetween(\DateTimeInterface $start, \DateTimeInterface $end): \Illuminate\Database\Eloquent\Collection
    {
        return static::whereBetween('date', [$start, $end])->orderBy('date')->get();
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    // Generer les jours feries recurrents pour une annee donnee
    public static function generateRecurringHolidays(int $year): void
    {
        $recurringHolidays = static::where('is_recurring', true)
            ->whereYear('date', '!=', $year)
            ->get();

        foreach ($recurringHolidays as $holiday) {
            $newDate = $holiday->date->setYear($year);

            static::firstOrCreate([
                'date' => $newDate,
            ], [
                'name' => $holiday->name,
                'year' => $year,
                'is_recurring' => true,
                'is_paid' => $holiday->is_paid,
            ]);
        }
    }
}
