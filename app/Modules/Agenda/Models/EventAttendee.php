<?php

namespace App\Modules\Agenda\Models;

use App\Modules\HR\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAttendee extends Model
{
    protected $fillable = [
        'event_id',
        'employee_id',
        'status',
        'is_organizer',
    ];

    protected $casts = [
        'is_organizer' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'accepted' => 'Accepté',
            'declined' => 'Refusé',
            'tentative' => 'Peut-être',
            default => 'Inconnu',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'accepted' => 'text-green-400',
            'declined' => 'text-red-400',
            'tentative' => 'text-yellow-400',
            default => 'text-gray-400',
        };
    }
}
