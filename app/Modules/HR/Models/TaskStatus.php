<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'order',
        'color',
        'is_default',
        'is_completed',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_completed' => 'boolean',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'status_id');
    }

    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }

    public function getColorClassAttribute(): string
    {
        return match ($this->color) {
            'slate' => 'bg-slate-500/20 text-slate-300',
            'blue' => 'bg-blue-500/20 text-blue-300',
            'amber' => 'bg-amber-500/20 text-amber-300',
            'green' => 'bg-green-500/20 text-green-300',
            'red' => 'bg-red-500/20 text-red-300',
            'purple' => 'bg-purple-500/20 text-purple-300',
            'pink' => 'bg-pink-500/20 text-pink-300',
            default => 'bg-gray-500/20 text-gray-300',
        };
    }

    public function getSelectClassAttribute(): string
    {
        return match ($this->color) {
            'slate' => 'bg-slate-700 text-slate-200',
            'blue' => 'bg-blue-700 text-blue-200',
            'amber' => 'bg-amber-700 text-amber-200',
            'green' => 'bg-green-700 text-green-200',
            'red' => 'bg-red-700 text-red-200',
            'purple' => 'bg-purple-700 text-purple-200',
            'pink' => 'bg-pink-700 text-pink-200',
            default => 'bg-gray-700 text-gray-200',
        };
    }

    public function getBgHexAttribute(): string
    {
        return match ($this->color) {
            'slate' => '#475569',
            'blue' => '#1d4ed8',
            'amber' => '#b45309',
            'green' => '#15803d',
            'red' => '#b91c1c',
            'purple' => '#7e22ce',
            'pink' => '#be185d',
            default => '#374151',
        };
    }

    public function getTextHexAttribute(): string
    {
        return '#ffffff';
    }

    public function getBorderColorClassAttribute(): string
    {
        return match ($this->color) {
            'slate' => 'border-slate-300',
            'blue' => 'border-blue-300',
            'amber' => 'border-amber-300',
            'green' => 'border-green-300',
            'red' => 'border-red-300',
            'purple' => 'border-purple-300',
            'pink' => 'border-pink-300',
            default => 'border-gray-300',
        };
    }
}
