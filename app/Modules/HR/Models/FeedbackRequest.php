<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'evaluation_period_id',
        'requested_from_id',
        'relationship',
        'status',
        'feedback',
        'overall_rating',
        'submitted_at',
        'declined_at',
        'decline_reason',
        'is_anonymous',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'declined_at' => 'datetime',
        'is_anonymous' => 'boolean',
    ];

    public const RELATIONSHIPS = [
        'manager' => 'Manager',
        'peer' => 'Collegue',
        'subordinate' => 'Subordonne',
        'cross_functional' => 'Autre departement',
    ];

    public const STATUSES = [
        'pending' => 'En attente',
        'submitted' => 'Soumis',
        'declined' => 'Decline',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function evaluationPeriod(): BelongsTo
    {
        return $this->belongsTo(EvaluationPeriod::class);
    }

    public function requestedFrom(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'requested_from_id');
    }

    public function getRelationshipLabelAttribute(): string
    {
        return self::RELATIONSHIPS[$this->relationship] ?? $this->relationship;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'amber',
            'submitted' => 'green',
            'declined' => 'red',
            default => 'gray',
        };
    }

    public function submit(string $feedback, int $rating): void
    {
        $this->update([
            'status' => 'submitted',
            'feedback' => $feedback,
            'overall_rating' => $rating,
            'submitted_at' => now(),
        ]);
    }

    public function decline(string $reason): void
    {
        $this->update([
            'status' => 'declined',
            'decline_reason' => $reason,
            'declined_at' => now(),
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeRequestedFrom($query, int $employeeId)
    {
        return $query->where('requested_from_id', $employeeId);
    }

    public function scopeForPeriod($query, int $periodId)
    {
        return $query->where('evaluation_period_id', $periodId);
    }
}
