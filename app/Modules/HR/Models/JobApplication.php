<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    protected $fillable = [
        'job_position_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'resume_path',
        'cover_letter',
        'status',
        'rating',
        'notes',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'applied_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($application) {
            if (empty($application->applied_at)) {
                $application->applied_at = now();
            }
        });
    }

    // Relations
    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

    // Scopes
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeReviewing($query)
    {
        return $query->where('status', 'reviewing');
    }

    public function scopeInterview($query)
    {
        return $query->where('status', 'interview');
    }

    public function scopeOffer($query)
    {
        return $query->where('status', 'offer');
    }

    public function scopeHired($query)
    {
        return $query->where('status', 'hired');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['hired', 'rejected']);
    }

    // Status helpers
    public function markAsReviewing(): void
    {
        $this->update(['status' => 'reviewing']);
    }

    public function scheduleInterview(): void
    {
        $this->update(['status' => 'interview']);
    }

    public function makeOffer(): void
    {
        $this->update(['status' => 'offer']);
    }

    public function hire(): void
    {
        $this->update(['status' => 'hired']);
    }

    public function reject(): void
    {
        $this->update(['status' => 'rejected']);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'new' => 'Nouvelle',
            'reviewing' => 'En revue',
            'interview' => 'Entretien',
            'offer' => 'Offre',
            'hired' => 'Embauché',
            'rejected' => 'Rejeté',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'new' => 'blue',
            'reviewing' => 'yellow',
            'interview' => 'purple',
            'offer' => 'green',
            'hired' => 'emerald',
            'rejected' => 'red',
            default => 'gray',
        };
    }

    // Create employee from hired application
    public function createEmployee(): Employee
    {
        return Employee::create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'department_id' => $this->jobPosition->department_id,
            'job_title' => $this->jobPosition->title,
            'hire_date' => now(),
            'contract_type' => $this->jobPosition->type === 'internship' ? 'stage' : 'cdi',
            'status' => 'active',
        ]);
    }
}
