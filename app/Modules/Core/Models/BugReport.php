<?php

namespace App\Modules\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BugReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'user_id',
        'type',
        'priority',
        'status',
        'title',
        'description',
        'page_url',
        'browser',
        'steps_to_reproduce',
        'expected_behavior',
        'actual_behavior',
        'screenshot_path',
        'admin_response',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            if (empty($report->reference)) {
                $lastReport = static::whereYear('created_at', now()->year)->latest('id')->first();
                $nextNumber = $lastReport ? intval(substr($lastReport->reference, -4)) + 1 : 1;
                $report->reference = 'BUG-' . now()->format('Y') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Accessors
    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'bug' => 'red',
            'improvement' => 'blue',
            'feature' => 'green',
            'question' => 'purple',
            default => 'gray',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'bug' => 'Bug',
            'improvement' => 'Amelioration',
            'feature' => 'Nouvelle fonctionnalite',
            'question' => 'Question',
            default => ucfirst($this->type),
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'bug' => 'bug_report',
            'improvement' => 'trending_up',
            'feature' => 'add_circle',
            'question' => 'help',
            default => 'report',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'gray',
            'normal' => 'blue',
            'high' => 'orange',
            'critical' => 'red',
            default => 'gray',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'Faible',
            'normal' => 'Normale',
            'high' => 'Haute',
            'critical' => 'Critique',
            default => ucfirst($this->priority),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open' => 'blue',
            'in_progress' => 'amber',
            'resolved' => 'green',
            'closed' => 'gray',
            'wont_fix' => 'red',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Ouvert',
            'in_progress' => 'En cours',
            'resolved' => 'Resolu',
            'closed' => 'Ferme',
            'wont_fix' => 'Ne sera pas corrige',
            default => ucfirst($this->status),
        };
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeCritical($query)
    {
        return $query->where('priority', 'critical');
    }
}
