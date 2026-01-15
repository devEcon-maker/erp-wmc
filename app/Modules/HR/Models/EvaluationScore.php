<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationScore extends Model
{
    protected $fillable = [
        'evaluation_id',
        'evaluation_criteria_id',
        'score',
        'comments',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriteria::class, 'evaluation_criteria_id');
    }

    public function getNormalizedScoreAttribute(): float
    {
        if (!$this->score || !$this->criteria) {
            return 0;
        }

        return ($this->score / $this->criteria->max_score) * 5;
    }

    public function getScorePercentageAttribute(): float
    {
        if (!$this->score || !$this->criteria) {
            return 0;
        }

        return round(($this->score / $this->criteria->max_score) * 100, 2);
    }

    public function getScoreLabelAttribute(): string
    {
        $percentage = $this->score_percentage;

        return match (true) {
            $percentage >= 90 => 'Exceptionnel',
            $percentage >= 75 => 'Tres bien',
            $percentage >= 60 => 'Bien',
            $percentage >= 40 => 'A ameliorer',
            default => 'Insuffisant',
        };
    }

    public function getScoreColorAttribute(): string
    {
        $percentage = $this->score_percentage;

        return match (true) {
            $percentage >= 90 => 'green',
            $percentage >= 75 => 'blue',
            $percentage >= 60 => 'amber',
            $percentage >= 40 => 'orange',
            default => 'red',
        };
    }
}
