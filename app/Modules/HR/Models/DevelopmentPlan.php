<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevelopmentPlan extends Model
{
    protected $fillable = [
        'employee_id',
        'evaluation_id',
        'title',
        'description',
        'skill_area',
        'action_type',
        'start_date',
        'target_date',
        'status',
        'progress_percentage',
        'resources_needed',
        'budget',
        'completion_notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'target_date' => 'date',
        'budget' => 'decimal:2',
    ];

    public const SKILL_AREAS = [
        'technical' => 'Technique',
        'leadership' => 'Leadership',
        'communication' => 'Communication',
        'management' => 'Management',
        'soft_skills' => 'Soft skills',
        'domain' => 'Expertise metier',
    ];

    public const ACTION_TYPES = [
        'training' => 'Formation',
        'mentoring' => 'Mentorat',
        'project' => 'Projet',
        'certification' => 'Certification',
        'course' => 'Cours en ligne',
        'coaching' => 'Coaching',
        'conference' => 'Conference',
        'reading' => 'Lecture',
    ];

    public const STATUSES = [
        'planned' => 'Planifie',
        'in_progress' => 'En cours',
        'completed' => 'Termine',
        'cancelled' => 'Annule',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getSkillAreaLabelAttribute(): ?string
    {
        return self::SKILL_AREAS[$this->skill_area] ?? $this->skill_area;
    }

    public function getActionTypeLabelAttribute(): string
    {
        return self::ACTION_TYPES[$this->action_type] ?? $this->action_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'planned' => 'gray',
            'in_progress' => 'blue',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'slate',
        };
    }

    public function getProgressColorAttribute(): string
    {
        $progress = $this->progress_percentage;
        return match (true) {
            $progress >= 100 => 'green',
            $progress >= 75 => 'blue',
            $progress >= 50 => 'amber',
            default => 'red',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'in_progress' && $this->target_date->isPast();
    }

    public function start(): void
    {
        $this->update(['status' => 'in_progress']);
    }

    public function updateProgress(int $percentage): void
    {
        $this->update(['progress_percentage' => min(100, max(0, $percentage))]);

        if ($percentage >= 100) {
            $this->complete();
        }
    }

    public function complete(?string $notes = null): void
    {
        $this->update([
            'status' => 'completed',
            'progress_percentage' => 100,
            'completion_notes' => $notes,
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function scopePlanned($query)
    {
        return $query->where('status', 'planned');
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

    public function scopeOverdue($query)
    {
        return $query->where('status', 'in_progress')
            ->where('target_date', '<', now());
    }
}
