<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayslipBonus extends Model
{
    protected $fillable = [
        'payslip_id',
        'bonus_type_id',
        'label',
        'amount',
        'is_taxable',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_taxable' => 'boolean',
    ];

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class);
    }

    public function bonusType(): BelongsTo
    {
        return $this->belongsTo(BonusType::class);
    }
}
