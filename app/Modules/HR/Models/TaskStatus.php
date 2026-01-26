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
            'slate' => 'bg-slate-100 text-slate-800',
            'blue' => 'bg-blue-100 text-blue-800',
            'amber' => 'bg-amber-100 text-amber-800',
            'green' => 'bg-green-100 text-green-800',
            'red' => 'bg-red-100 text-red-800',
            'purple' => 'bg-purple-100 text-purple-800',
            'pink' => 'bg-pink-100 text-pink-800',
            default => 'bg-gray-100 text-gray-800',
        };
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
