<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationCriteria extends Model
{
    protected $table = 'evaluation_criteria';

    protected $fillable = [
        'evaluation_template_id',
        'category',
        'name',
        'description',
        'weight',
        'max_score',
        'order',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public const CATEGORIES = [
        'technical' => 'Competences techniques',
        'behavioral' => 'Competences comportementales',
        'goals' => 'Objectifs',
        'competencies' => 'Competences generales',
        'leadership' => 'Leadership',
        'communication' => 'Communication',
        'teamwork' => 'Travail en equipe',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(EvaluationTemplate::class, 'evaluation_template_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(EvaluationScore::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function getWeightPercentageAttribute(): float
    {
        $totalWeight = $this->template->total_weight;
        if ($totalWeight === 0) {
            return 0;
        }
        return round(($this->weight / $totalWeight) * 100, 2);
    }
}
