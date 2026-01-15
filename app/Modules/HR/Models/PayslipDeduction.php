<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayslipDeduction extends Model
{
    protected $fillable = [
        'payslip_id',
        'deduction_type_id',
        'label',
        'base',
        'rate',
        'employee_amount',
        'employer_amount',
    ];

    protected $casts = [
        'base' => 'decimal:2',
        'rate' => 'decimal:2',
        'employee_amount' => 'decimal:2',
        'employer_amount' => 'decimal:2',
    ];

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class);
    }

    public function deductionType(): BelongsTo
    {
        return $this->belongsTo(DeductionType::class);
    }

    public function getTotalAmountAttribute(): float
    {
        return $this->employee_amount + $this->employer_amount;
    }
}
