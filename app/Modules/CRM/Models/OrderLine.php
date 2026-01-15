<?php

namespace App\Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Inventory\Models\Product;

class OrderLine extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'description',
        'quantity',
        'delivered_quantity',
        'unit_price',
        'tax_rate',
        'discount_rate',
        'total_amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'delivered_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
