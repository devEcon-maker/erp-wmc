<?php

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Inventory\Models\Product;

class SubscriptionLine extends Model
{
    protected $fillable = [
        'subscription_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'discount_rate',
        'total_ht',
        'total_ttc',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'total_ht' => 'decimal:2',
        'total_ttc' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($line) {
            // Calculer les totaux
            $subtotal = $line->quantity * $line->unit_price;
            $discount = $subtotal * ($line->discount_rate / 100);
            $line->total_ht = $subtotal - $discount;
            $line->total_ttc = $line->total_ht * (1 + $line->tax_rate / 100);
        });

        static::saved(function ($line) {
            // Recalculer les totaux de l'abonnement parent
            $line->subscription->recalculateTotals();
        });

        static::deleted(function ($line) {
            // Recalculer les totaux de l'abonnement parent
            if ($line->subscription) {
                $line->subscription->recalculateTotals();
            }
        });
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
