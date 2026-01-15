<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseLine extends Model
{
    protected $fillable = [
        'expense_report_id',
        'category_id',
        'date',
        'description',
        'amount',
        'receipt_path',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function expenseReport(): BelongsTo
    {
        return $this->belongsTo(ExpenseReport::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    // Validation helpers
    public function isOverLimit(): bool
    {
        if (!$this->category || !$this->category->max_amount) {
            return false;
        }
        return $this->amount > $this->category->max_amount;
    }

    public function needsReceipt(): bool
    {
        return $this->category && $this->category->requires_receipt && empty($this->receipt_path);
    }
}
