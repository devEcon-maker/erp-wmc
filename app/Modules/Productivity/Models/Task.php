<?php

namespace App\Modules\Productivity\Models;

use App\Modules\HR\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'parent_id',
        'title',
        'description',
        'assigned_to',
        'priority',
        'status',
        'start_date',
        'due_date',
        'estimated_hours',
        'order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'estimated_hours' => 'decimal:2',
    ];

    // Relations
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    // Accessors
    public function getActualHoursAttribute(): float
    {
        return $this->timeEntries()->sum('hours') ?? 0;
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->due_date || $this->status === 'done') {
            return false;
        }

        return $this->due_date->lt(now()->startOfDay());
    }

    public function getHoursVarianceAttribute(): float
    {
        if (!$this->estimated_hours) {
            return 0;
        }

        return $this->actual_hours - $this->estimated_hours;
    }

    public function getHoursProgressAttribute(): int
    {
        if (!$this->estimated_hours || $this->estimated_hours <= 0) {
            return 0;
        }

        return min(100, (int) (($this->actual_hours / $this->estimated_hours) * 100));
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'Basse',
            'medium' => 'Moyenne',
            'high' => 'Haute',
            'urgent' => 'Urgente',
            default => ucfirst($this->priority),
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'gray',
            'medium' => 'blue',
            'high' => 'yellow',
            'urgent' => 'red',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'todo' => 'À faire',
            'in_progress' => 'En cours',
            'review' => 'En revue',
            'done' => 'Terminée',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'todo' => 'gray',
            'in_progress' => 'blue',
            'review' => 'yellow',
            'done' => 'green',
            default => 'gray',
        };
    }

    // Scopes
    public function scopeTodo($query)
    {
        return $query->where('status', 'todo');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeInReview($query)
    {
        return $query->where('status', 'review');
    }

    public function scopeDone($query)
    {
        return $query->where('status', 'done');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'done')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->startOfDay());
    }

    public function scopeAssignedTo($query, $employeeId)
    {
        return $query->where('assigned_to', $employeeId);
    }

    public function scopeRootTasks($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at');
    }
}
