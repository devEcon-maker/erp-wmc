<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'address', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function stockLevels(): HasMany
    {
        return $this->hasMany(StockLevel::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
    
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
