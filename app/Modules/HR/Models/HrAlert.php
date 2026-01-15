<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrAlert extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'title',
        'description',
        'alert_date',
        'due_date',
        'priority',
        'status',
        'assigned_to',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
        'is_recurring',
        'recurrence_type',
    ];

    protected $casts = [
        'alert_date' => 'date',
        'due_date' => 'date',
        'resolved_at' => 'datetime',
        'is_recurring' => 'boolean',
    ];

    public const TYPES = [
        'contract_expiry' => 'Expiration de contrat',
        'probation_end' => 'Fin de periode d\'essai',
        'document_expiry' => 'Expiration de document',
        'birthday' => 'Anniversaire',
        'work_anniversary' => 'Anniversaire de travail',
        'leave_balance_low' => 'Solde conges faible',
        'evaluation_due' => 'Evaluation a faire',
        'training_due' => 'Formation a planifier',
        'loan_payment_due' => 'Echeance de pret',
        'custom' => 'Personnalisee',
    ];

    public const PRIORITIES = [
        'low' => 'Basse',
        'medium' => 'Moyenne',
        'high' => 'Haute',
        'critical' => 'Critique',
    ];

    public const STATUSES = [
        'pending' => 'En attente',
        'acknowledged' => 'Prise en compte',
        'resolved' => 'Resolue',
        'dismissed' => 'Ignoree',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'gray',
            'medium' => 'blue',
            'high' => 'amber',
            'critical' => 'red',
            default => 'slate',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'amber',
            'acknowledged' => 'blue',
            'resolved' => 'green',
            'dismissed' => 'gray',
            default => 'slate',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'contract_expiry' => 'description',
            'probation_end' => 'hourglass_empty',
            'document_expiry' => 'folder_open',
            'birthday' => 'cake',
            'work_anniversary' => 'celebration',
            'leave_balance_low' => 'event_busy',
            'evaluation_due' => 'assessment',
            'training_due' => 'school',
            'loan_payment_due' => 'payments',
            default => 'notifications',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status === 'pending';
    }

    public function resolve(User $user, ?string $notes = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_by' => $user->id,
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    public function dismiss(User $user, ?string $notes = null): void
    {
        $this->update([
            'status' => 'dismissed',
            'resolved_by' => $user->id,
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    public function acknowledge(): void
    {
        $this->update(['status' => 'acknowledged']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'acknowledged']);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeUpcoming($query, int $days = 7)
    {
        return $query->where('alert_date', '<=', now()->addDays($days))
            ->where('alert_date', '>=', now());
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }
}
