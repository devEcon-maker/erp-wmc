<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    protected $fillable = [
        'name',
        'max_amount',
        'requires_receipt',
    ];

    protected function casts(): array
    {
        return [
            'max_amount' => 'decimal:2',
            'requires_receipt' => 'boolean',
        ];
    }

    public function expenseLines(): HasMany
    {
        return $this->hasMany(ExpenseLine::class, 'category_id');
    }
}
