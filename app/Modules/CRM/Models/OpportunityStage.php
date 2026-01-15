<?php

namespace App\Modules\CRM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpportunityStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'order',
        'probability',
        'color',
    ];

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class, 'stage_id');
    }
}
