<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeObjective extends Model
{
    protected $fillable = [
        'employee_id',
        'evaluation_period_id',
        'title',
        'description',
        'category',
        'type',
        'metric',
        'target_value',
        'current_value',
        'unit',
        'weight',
        'start_date',
        'due_date',
        'status',
        'progress_percentage',
        'achievement_score',
        'manager_comments',
        'employee_comments',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    public const CATEGORIES = [
        'business' => 'Business',
        'development' => 'Developpement personnel',
        'team' => 'Equipe',
        'innovation' => 'Innovation',
        'quality' => 'Qualite',
    ];

    public const TYPES = [
        'quantitative' => 'Quantitatif',
        'qualitative' => 'Qualitatif',
    ];

    public const STATUSES = [
        'draft' => 'Brouillon',
        'in_progress' => 'En cours',
        'completed' => 'Termine',
        'cancelled' => 'Annule',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function evaluationPeriod(): BelongsTo
    {
        return $this->belongsTo(EvaluationPeriod::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getCategoryLabelAttribute(): ?string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
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
            'draft' => 'gray',
            'in_progress' => 'blue',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'slate',
        };
    }

    public function getProgressColorAttribute(): string
    {
        $progress = $this->progress_percentage;
        if ($progress >= 100) {
            return 'green';
        }
        if ($progress >= 75) {
            return 'blue';
        }
        if ($progress >= 50) {
            return 'amber';
        }
        return 'red';
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'in_progress' && $this->due_date->isPast();
    }

    public function getCalculatedProgressAttribute(): float
    {
        if ($this->type !== 'quantitative' || !$this->target_value) {
            return $this->progress_percentage;
        }

        return min(100, round(($this->current_value / $this->target_value) * 100, 2));
    }

    public function updateProgress(): void
    {
        if ($this->type === 'quantitative' && $this->target_value) {
            $this->progress_percentage = $this->calculated_progress;
            $this->save();
        }
    }

    public function complete(int $score = null): void
    {
        $this->update([
            'status' => 'completed',
            'progress_percentage' => 100,
            'achievement_score' => $score,
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForPeriod($query, int $periodId)
    {
        return $query->where('evaluation_period_id', $periodId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'in_progress')
            ->where('due_date', '<', now());
    }
}
