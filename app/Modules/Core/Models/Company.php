<?php

namespace App\Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'legal_form',
        'capital',
        'logo',
        'address',
        'city',
        'country',
        'postal_code',
        'phone',
        'email',
        'siret',
        'tva_number',
        'rccm',
        'bank_name',
        'bank_account',
        'iban',
        'swift',
        'website',
        'invoice_footer',
        'invoice_terms',
    ];

    protected $casts = [
        'capital' => 'decimal:2',
    ];

    /**
     * Get formatted capital
     */
    public function getFormattedCapitalAttribute(): string
    {
        return number_format($this->capital ?? 0, 0, ',', '.') . ' FCFA';
    }
}
