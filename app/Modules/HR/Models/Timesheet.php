<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timesheet extends Model
{
    protected $fillable = [
        'employee_id',
        'project_id',
        'task_id',
        'date',
        'hours',
        'description',
        'billable',
        'approved',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'hours' => 'decimal:2',
            'billable' => 'boolean',
            'approved' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    // Relations
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Productivity\Models\Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Productivity\Models\Task::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForWeek($query, $weekStart)
    {
        $weekEnd = $weekStart->copy()->addDays(6);
        return $query->whereBetween('date', [$weekStart, $weekEnd]);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeBillable($query)
    {
        return $query->where('billable', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('approved', false);
    }

    // Validation
    public static function getTotalHoursForDay($employeeId, $date): float
    {
        return self::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->sum('hours');
    }

    public function canAdd(): bool
    {
        // Max 24h par jour
        $currentTotal = self::getTotalHoursForDay($this->employee_id, $this->date);
        return ($currentTotal + $this->hours) <= 24;
    }

    public static function isDateInFuture($date): bool
    {
        return $date > now()->toDateString();
    }
}
