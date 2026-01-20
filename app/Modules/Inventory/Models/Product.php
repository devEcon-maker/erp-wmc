<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'reference',
        'name',
        'description',
        'category_id',
        'purchase_price',
        'selling_price',
        'tax_rate',
        'unit',
        'is_active',
        'track_stock',
        'min_stock_alert',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'track_stock' => 'boolean',
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
        ];
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function stockLevels()
    {
        return $this->hasMany(\App\Modules\Inventory\Models\StockLevel::class);
    }

    public function movements()
    {
        return $this->hasMany(\App\Modules\Inventory\Models\StockMovement::class);
    }

    // Accessors
    public function getMarginAttribute()
    {
        return $this->selling_price - $this->purchase_price;
    }

    public function getMarginPercentAttribute()
    {
        if ($this->purchase_price > 0) {
            return ($this->margin / $this->purchase_price) * 100;
        }
        return 100;
    }

    public function getCurrentStockAttribute()
    {
        if (!$this->track_stock) {
            return null;
        }

        // Calculer le stock à partir des mouvements
        $stockIn = $this->movements()->where('type', 'in')->sum('quantity');
        $stockOut = $this->movements()->where('type', 'out')->sum('quantity');

        return $stockIn - $stockOut;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeProducts($query)
    {
        return $query->where('type', 'product');
    }

    public function scopeServices($query)
    {
        return $query->where('type', 'service');
    }

    public function scopeTrackStock($query)
    {
        return $query->where('track_stock', true);
    }

    protected static function newFactory()
    {
        return \App\Modules\Inventory\Database\Factories\ProductFactory::new();
    }
}
