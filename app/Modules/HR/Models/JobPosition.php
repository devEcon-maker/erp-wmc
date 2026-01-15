<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPosition extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'department_id',
        'description',
        'requirements',
        'type',
        'location',
        'salary_range_min',
        'salary_range_max',
        'status',
        'published_at',
        'closes_at',
    ];

    protected function casts(): array
    {
        return [
            'salary_range_min' => 'decimal:2',
            'salary_range_max' => 'decimal:2',
            'published_at' => 'datetime',
            'closes_at' => 'date',
        ];
    }

    // Relations
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('closes_at')
                    ->orWhere('closes_at', '>=', now());
            });
    }

    // Helpers
    public function publish(): bool
    {
        if ($this->status !== 'draft') {
            return false;
        }

        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return true;
    }

    public function close(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        $this->update(['status' => 'closed']);
        return true;
    }

    public function unpublish(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        $this->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        return true;
    }

    // Accessors
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'full_time' => 'Temps plein',
            'part_time' => 'Temps partiel',
            'contract' => 'CDD',
            'internship' => 'Stage',
            default => $this->type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Brouillon',
            'published' => 'Publié',
            'closed' => 'Clôturé',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'published' => 'green',
            'closed' => 'red',
            default => 'gray',
        };
    }

    public function getSalaryRangeAttribute(): ?string
    {
        if (!$this->salary_range_min && !$this->salary_range_max) {
            return null;
        }

        if ($this->salary_range_min && $this->salary_range_max) {
            return number_format($this->salary_range_min, 0, ',', ' ') . ' - ' .
                number_format($this->salary_range_max, 0, ',', ' ') . ' FCFA';
        }

        if ($this->salary_range_min) {
            return 'À partir de ' . number_format($this->salary_range_min, 0, ',', ' ') . ' FCFA';
        }

        return 'Jusqu\'à ' . number_format($this->salary_range_max, 0, ',', ' ') . ' FCFA';
    }

    public function getApplicationsCountAttribute(): int
    {
        return $this->applications()->count();
    }
}
