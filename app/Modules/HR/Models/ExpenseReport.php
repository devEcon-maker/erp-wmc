<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'reference',
        'period_start',
        'period_end',
        'status',
        'total_amount',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'total_amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    // Génération automatique de la référence
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            if (empty($report->reference)) {
                $report->reference = self::generateReference();
            }
        });
    }

    public static function generateReference(): string
    {
        $prefix = 'NDF-' . date('Ym') . '-';
        $lastReport = self::where('reference', 'like', $prefix . '%')
            ->orderBy('reference', 'desc')
            ->first();

        if ($lastReport) {
            $lastNumber = (int) substr($lastReport->reference, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // Relations
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ExpenseLine::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'submitted']);
    }

    // Helpers
    public function canEdit(): bool
    {
        return $this->status === 'draft';
    }

    public function canSubmit(): bool
    {
        return $this->status === 'draft' && $this->lines()->count() > 0;
    }

    public function canApprove(): bool
    {
        return $this->status === 'submitted';
    }

    public function canPay(): bool
    {
        return $this->status === 'approved';
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Brouillon',
            'submitted' => 'Soumise',
            'approved' => 'Approuvée',
            'rejected' => 'Rejetée',
            'paid' => 'Payée',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'submitted' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            'paid' => 'blue',
            default => 'gray',
        };
    }
}
