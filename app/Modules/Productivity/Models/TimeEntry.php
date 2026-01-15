<?php

namespace App\Modules\Productivity\Models;

use App\Modules\HR\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
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

    protected $casts = [
        'date' => 'date',
        'hours' => 'decimal:2',
        'billable' => 'boolean',
        'approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    // Relations
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Accessors
    public function getCostAttribute(): float
    {
        $memberRate = $this->project?->members()
            ->where('employee_id', $this->employee_id)
            ->first()?->pivot?->hourly_rate;

        $rate = $memberRate ?? $this->project?->hourly_rate ?? 0;

        return $this->hours * $rate;
    }

    // Scopes
    public function scopeBillable($query)
    {
        return $query->where('billable', true);
    }

    public function scopeNonBillable($query)
    {
        return $query->where('billable', false);
    }

    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('approved', false);
    }

    public function scopeForPeriod($query, $start, $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }
}
