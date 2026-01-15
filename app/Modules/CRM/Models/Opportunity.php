<?php

namespace App\Modules\CRM\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Opportunity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contact_id',
        'title',
        'description',
        'amount',
        'probability',
        'stage_id',
        'expected_close_date',
        'assigned_to',
        'won_at',
        'lost_at',
        'lost_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expected_close_date' => 'date',
        'won_at' => 'datetime',
        'lost_at' => 'datetime',
        'probability' => 'integer',
    ];

    // Accessors

    public function getWeightedAmountAttribute()
    {
        return $this->amount * ($this->probability / 100);
    }

    public function getStatusAttribute()
    {
        if ($this->won_at) {
            return 'won';
        }
        if ($this->lost_at) {
            return 'lost';
        }
        return 'open';
    }

    // Scopes

    public function scopeOpen(Builder $query)
    {
        return $query->whereNull('won_at')->whereNull('lost_at');
    }

    public function scopeWon(Builder $query)
    {
        return $query->whereNotNull('won_at');
    }

    public function scopeLost(Builder $query)
    {
        return $query->whereNotNull('lost_at');
    }

    public function scopeByStage(Builder $query, $stageId)
    {
        return $query->where('stage_id', $stageId);
    }

    // Relationships

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function stage()
    {
        return $this->belongsTo(OpportunityStage::class, 'stage_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
