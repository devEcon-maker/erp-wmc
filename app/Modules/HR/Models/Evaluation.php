<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluation extends Model
{
    protected $fillable = [
        'employee_id',
        'evaluation_period_id',
        'evaluation_template_id',
        'evaluator_id',
        'evaluator_type',
        'status',
        'overall_score',
        'overall_rating',
        'strengths',
        'areas_for_improvement',
        'development_plan',
        'manager_comments',
        'employee_comments',
        'submitted_at',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'overall_score' => 'decimal:2',
        'submitted_at' => 'datetime',
        'validated_at' => 'datetime',
    ];

    public const EVALUATOR_TYPES = [
        'self' => 'Auto-evaluation',
        'manager' => 'Manager',
        'peer' => 'Collegue',
        'subordinate' => 'Subordonne',
    ];

    public const STATUSES = [
        'draft' => 'Brouillon',
        'in_progress' => 'En cours',
        'submitted' => 'Soumis',
        'validated' => 'Valide',
    ];

    public const RATINGS = [
        'A' => 'Exceptionnel',
        'B' => 'Depasse les attentes',
        'C' => 'Repond aux attentes',
        'D' => 'A ameliorer',
        'E' => 'Insuffisant',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function evaluationPeriod(): BelongsTo
    {
        return $this->belongsTo(EvaluationPeriod::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EvaluationTemplate::class, 'evaluation_template_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'evaluator_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(EvaluationScore::class);
    }

    public function getEvaluatorTypeLabelAttribute(): string
    {
        return self::EVALUATOR_TYPES[$this->evaluator_type] ?? $this->evaluator_type;
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
            'submitted' => 'amber',
            'validated' => 'green',
            default => 'slate',
        };
    }

    public function getRatingLabelAttribute(): ?string
    {
        return self::RATINGS[$this->overall_rating] ?? $this->overall_rating;
    }

    public function getRatingColorAttribute(): string
    {
        return match ($this->overall_rating) {
            'A' => 'green',
            'B' => 'blue',
            'C' => 'amber',
            'D' => 'orange',
            'E' => 'red',
            default => 'gray',
        };
    }

    public function getCompletionRateAttribute(): float
    {
        $totalCriteria = $this->template->criteria()->count();
        if ($totalCriteria === 0) {
            return 0;
        }

        $scoredCriteria = $this->scores()->whereNotNull('score')->count();
        return round(($scoredCriteria / $totalCriteria) * 100, 2);
    }

    public function calculateOverallScore(): float
    {
        $scores = $this->scores()->with('criteria')->get();
        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($scores as $score) {
            if ($score->score !== null) {
                $weight = $score->criteria->weight;
                $maxScore = $score->criteria->max_score;
                $normalizedScore = ($score->score / $maxScore) * 5; // Normaliser sur 5

                $weightedSum += $normalizedScore * $weight;
                $totalWeight += $weight;
            }
        }

        if ($totalWeight === 0) {
            return 0;
        }

        return round($weightedSum / $totalWeight, 2);
    }

    public function calculateRating(): string
    {
        $score = $this->overall_score ?? $this->calculateOverallScore();

        return match (true) {
            $score >= 4.5 => 'A',
            $score >= 3.5 => 'B',
            $score >= 2.5 => 'C',
            $score >= 1.5 => 'D',
            default => 'E',
        };
    }

    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'in_progress']);
    }

    public function canSubmit(): bool
    {
        return $this->status === 'in_progress' && $this->completion_rate >= 100;
    }

    public function submit(): void
    {
        $this->overall_score = $this->calculateOverallScore();
        $this->overall_rating = $this->calculateRating();
        $this->status = 'submitted';
        $this->submitted_at = now();
        $this->save();
    }

    public function validate(User $validator): void
    {
        $this->update([
            'status' => 'validated',
            'validated_by' => $validator->id,
            'validated_at' => now(),
        ]);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeValidated($query)
    {
        return $query->where('status', 'validated');
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForEvaluator($query, int $evaluatorId)
    {
        return $query->where('evaluator_id', $evaluatorId);
    }

    public function scopeForPeriod($query, int $periodId)
    {
        return $query->where('evaluation_period_id', $periodId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('evaluator_type', $type);
    }
}
