<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'is_active',
        'include_self_evaluation',
        'include_manager_evaluation',
        'include_peer_evaluation',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'include_self_evaluation' => 'boolean',
        'include_manager_evaluation' => 'boolean',
        'include_peer_evaluation' => 'boolean',
    ];

    public const TYPES = [
        'performance' => 'Performance',
        'probation' => 'Fin de periode d\'essai',
        '360' => 'Evaluation 360',
        'skills' => 'Competences',
    ];

    public function criteria(): HasMany
    {
        return $this->hasMany(EvaluationCriteria::class)->orderBy('order');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getCriteriaCountAttribute(): int
    {
        return $this->criteria()->count();
    }

    public function getTotalWeightAttribute(): int
    {
        return $this->criteria()->sum('weight');
    }

    public function getCategoriesAttribute(): array
    {
        return $this->criteria()
            ->distinct()
            ->pluck('category')
            ->toArray();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
