<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeductionType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'calculation_type',
        'default_amount',
        'percentage',
        'base',
        'employee_rate',
        'employer_rate',
        'ceiling',
        'is_mandatory',
        'is_active',
        'order',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'employee_rate' => 'decimal:2',
        'employer_rate' => 'decimal:2',
        'ceiling' => 'decimal:2',
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    public const CALCULATION_TYPES = [
        'fixed' => 'Montant fixe',
        'percentage' => 'Pourcentage',
        'formula' => 'Formule',
    ];

    public const BASES = [
        'gross_salary' => 'Salaire brut',
        'base_salary' => 'Salaire de base',
        'taxable_income' => 'Revenu imposable',
    ];

    public function payslipDeductions(): HasMany
    {
        return $this->hasMany(PayslipDeduction::class);
    }

    public function getCalculationTypeLabelAttribute(): string
    {
        return self::CALCULATION_TYPES[$this->calculation_type] ?? $this->calculation_type;
    }

    public function getBaseLabelAttribute(): ?string
    {
        return self::BASES[$this->base] ?? $this->base;
    }

    public function calculateEmployeeAmount(float $baseAmount): float
    {
        // Appliquer le plafond si defini
        $base = $this->ceiling ? min($baseAmount, $this->ceiling) : $baseAmount;

        return match ($this->calculation_type) {
            'fixed' => $this->default_amount ?? 0,
            'percentage' => ($base * ($this->employee_rate ?? 0)) / 100,
            default => $this->default_amount ?? 0,
        };
    }

    public function calculateEmployerAmount(float $baseAmount): float
    {
        // Appliquer le plafond si defini
        $base = $this->ceiling ? min($baseAmount, $this->ceiling) : $baseAmount;

        return match ($this->calculation_type) {
            'fixed' => 0, // Charges patronales sur montant fixe generalement 0
            'percentage' => ($base * ($this->employer_rate ?? 0)) / 100,
            default => 0,
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
