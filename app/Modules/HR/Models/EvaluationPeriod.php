<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationPeriod extends Model
{
    protected $fillable = [
        'name',
        'type',
        'start_date',
        'end_date',
        'evaluation_start_date',
        'evaluation_end_date',
        'status',
        'description',
        'evaluation_template_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'evaluation_start_date' => 'date',
        'evaluation_end_date' => 'date',
    ];

    public const TYPES = [
        'annual' => 'Annuelle',
        'semi_annual' => 'Semestrielle',
        'quarterly' => 'Trimestrielle',
        'probation' => 'Fin de periode d\'essai',
    ];

    public const STATUSES = [
        'draft' => 'Brouillon',
        'open' => 'Ouverte',
        'closed' => 'Fermee',
        'archived' => 'Archivee',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(EvaluationTemplate::class, 'evaluation_template_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(EmployeeObjective::class);
    }

    public function feedbackRequests(): HasMany
    {
        return $this->hasMany(FeedbackRequest::class);
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
            'open' => 'green',
            'closed' => 'amber',
            'archived' => 'purple',
            default => 'slate',
        };
    }

    public function getIsOpenAttribute(): bool
    {
        return $this->status === 'open';
    }

    public function getIsEvaluationPeriodActiveAttribute(): bool
    {
        if ($this->status !== 'open') {
            return false;
        }

        $now = now();
        return $now->between($this->evaluation_start_date, $this->evaluation_end_date);
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->evaluation_end_date->isPast()) {
            return 0;
        }
        return now()->diffInDays($this->evaluation_end_date);
    }

    public function getCompletionRateAttribute(): float
    {
        $total = $this->evaluations()->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->evaluations()
            ->whereIn('status', ['submitted', 'validated'])
            ->count();

        return round(($completed / $total) * 100, 2);
    }

    public function open(): void
    {
        $this->update(['status' => 'open']);
    }

    public function close(): void
    {
        $this->update(['status' => 'closed']);
    }

    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeCurrent($query)
    {
        return $query->where('status', 'open')
            ->where('evaluation_start_date', '<=', now())
            ->where('evaluation_end_date', '>=', now());
    }

    public static function getCurrent(): ?self
    {
        return static::current()->first();
    }
}
