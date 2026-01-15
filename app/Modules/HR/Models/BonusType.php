<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BonusType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'calculation_type',
        'default_amount',
        'percentage',
        'is_taxable',
        'is_recurring',
        'frequency',
        'is_active',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
    ];

    public const CALCULATION_TYPES = [
        'fixed' => 'Montant fixe',
        'percentage' => 'Pourcentage',
        'formula' => 'Formule',
    ];

    public const FREQUENCIES = [
        'monthly' => 'Mensuelle',
        'quarterly' => 'Trimestrielle',
        'semi_annual' => 'Semestrielle',
        'yearly' => 'Annuelle',
        'one_time' => 'Ponctuelle',
    ];

    public function payslipBonuses(): HasMany
    {
        return $this->hasMany(PayslipBonus::class);
    }

    public function employeeBonuses(): HasMany
    {
        return $this->hasMany(EmployeeBonus::class);
    }

    public function getCalculationTypeLabelAttribute(): string
    {
        return self::CALCULATION_TYPES[$this->calculation_type] ?? $this->calculation_type;
    }

    public function getFrequencyLabelAttribute(): ?string
    {
        return self::FREQUENCIES[$this->frequency] ?? $this->frequency;
    }

    public function calculateAmount(float $baseSalary): float
    {
        return match ($this->calculation_type) {
            'fixed' => $this->default_amount ?? 0,
            'percentage' => ($baseSalary * ($this->percentage ?? 0)) / 100,
            default => $this->default_amount ?? 0,
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeTaxable($query)
    {
        return $query->where('is_taxable', true);
    }
}
