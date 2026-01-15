<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class EmployeeContract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'reference',
        'type',
        'contract_type',
        'start_date',
        'end_date',
        'probation_end_date',
        'probation_duration_days',
        'salary',
        'terms',
        'document_path',
        'status',
        'signed_date',
        'signed_by',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'probation_end_date' => 'date',
        'signed_date' => 'date',
        'salary' => 'decimal:2',
    ];

    public const TYPES = [
        'initial' => 'Contrat initial',
        'renewal' => 'Renouvellement',
        'amendment' => 'Avenant',
    ];

    public const CONTRACT_TYPES = [
        'cdi' => 'CDI',
        'cdd' => 'CDD',
        'interim' => 'Interim',
        'stage' => 'Stage',
        'alternance' => 'Alternance',
    ];

    public const STATUSES = [
        'draft' => 'Brouillon',
        'active' => 'Actif',
        'expired' => 'Expire',
        'terminated' => 'Resilie',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($contract) {
            if (empty($contract->reference)) {
                $year = now()->format('Y');
                $lastContract = static::withTrashed()
                    ->whereYear('created_at', $year)
                    ->orderBy('id', 'desc')
                    ->first();
                $lastNum = $lastContract ? (int) substr($lastContract->reference, -4) : 0;
                $contract->reference = 'CTR-' . $year . '-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function signedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getContractTypeLabelAttribute(): string
    {
        return self::CONTRACT_TYPES[$this->contract_type] ?? $this->contract_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'active' => 'green',
            'expired' => 'amber',
            'terminated' => 'red',
            default => 'slate',
        };
    }

    public function getDocumentUrlAttribute(): ?string
    {
        return $this->document_path ? Storage::url($this->document_path) : null;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getIsExpiringAttribute(): bool
    {
        return $this->end_date &&
            $this->status === 'active' &&
            $this->end_date->isBetween(now(), now()->addDays(30));
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function getDurationMonthsAttribute(): ?int
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->diffInMonths($this->end_date);
        }
        return null;
    }

    public function getIsProbationActiveAttribute(): bool
    {
        return $this->probation_end_date &&
            $this->probation_end_date->isFuture() &&
            $this->status === 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiring($query, int $days = 30)
    {
        return $query->where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '>=', now())
            ->where('end_date', '<=', now()->addDays($days));
    }

    public function scopeProbationEnding($query, int $days = 30)
    {
        return $query->where('status', 'active')
            ->whereNotNull('probation_end_date')
            ->where('probation_end_date', '>=', now())
            ->where('probation_end_date', '<=', now()->addDays($days));
    }
}
