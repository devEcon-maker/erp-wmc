<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employee_tasks';

    protected $fillable = [
        'title',
        'description',
        'status_id',
        'employee_id',
        'assigned_by',
        'priority',
        'due_date',
        'started_at',
        'completed_at',
        'estimated_hours',
        'actual_hours',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const PRIORITIES = [
        'low' => 'Basse',
        'medium' => 'Moyenne',
        'high' => 'Haute',
        'urgent' => 'Urgente',
    ];

    // Relations

    public function status(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class, 'status_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'task_assignees', 'task_id', 'employee_id')
            ->withPivot(['assigned_at', 'assigned_by'])
            ->withTimestamps();
    }

    // Accesseurs

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'gray',
            'medium' => 'blue',
            'high' => 'amber',
            'urgent' => 'red',
            default => 'gray',
        };
    }

    public function getPriorityClassAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'bg-gray-100 text-gray-800',
            'medium' => 'bg-blue-100 text-blue-800',
            'high' => 'bg-amber-100 text-amber-800',
            'urgent' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date &&
               $this->due_date->isPast() &&
               !$this->status?->is_completed;
    }

    public function getIsDueSoonAttribute(): bool
    {
        return $this->due_date &&
               $this->due_date->isBetween(now(), now()->addDays(2)) &&
               !$this->status?->is_completed;
    }

    public function getProgressPercentAttribute(): int
    {
        if (!$this->estimated_hours || $this->estimated_hours == 0) {
            return 0;
        }
        $actual = $this->actual_hours ?? 0;
        return min(100, round(($actual / $this->estimated_hours) * 100));
    }

    // Scopes

    public function scopeForEmployee(Builder $query, int $employeeId): Builder
    {
        return $query->where(function ($q) use ($employeeId) {
            $q->where('employee_id', $employeeId)
              ->orWhereHas('assignees', function ($aq) use ($employeeId) {
                  $aq->where('employees.id', $employeeId);
              });
        });
    }

    public function scopeByStatus(Builder $query, int $statusId): Builder
    {
        return $query->where('status_id', $statusId);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereHas('status', function ($q) {
            $q->where('is_completed', false);
        });
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereHas('status', function ($q) {
            $q->where('is_completed', true);
        });
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereHas('status', function ($q) {
                $q->where('is_completed', false);
            });
    }

    public function scopeDueSoon(Builder $query, int $days = 7): Builder
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays($days))
            ->whereHas('status', function ($q) {
                $q->where('is_completed', false);
            });
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    // Methodes

    public function markAsStarted(): void
    {
        if (!$this->started_at) {
            $this->update(['started_at' => now()]);
        }
    }

    public function markAsCompleted(): void
    {
        $completedStatus = TaskStatus::where('slug', 'completed')->first();
        if ($completedStatus) {
            $this->update([
                'status_id' => $completedStatus->id,
                'completed_at' => now(),
            ]);
        }
    }

    public function updateStatus(int $statusId): void
    {
        $status = TaskStatus::find($statusId);
        $updates = ['status_id' => $statusId];

        // Si on passe en "En cours" et pas encore commence
        if ($status && $status->slug === 'in_progress' && !$this->started_at) {
            $updates['started_at'] = now();
        }

        // Si on passe en "Terminee"
        if ($status && $status->is_completed && !$this->completed_at) {
            $updates['completed_at'] = now();
        }

        // Si on repasse en non-complete, on enleve completed_at
        if ($status && !$status->is_completed) {
            $updates['completed_at'] = null;
        }

        $this->update($updates);
    }
}
