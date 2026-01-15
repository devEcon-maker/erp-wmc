<?php

namespace App\Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\User;

class Reminder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contact_id',
        'created_by',
        'reminder_type',
        'remindable_type',
        'remindable_id',
        'subject',
        'message',
        'channel',
        'status',
        'scheduled_at',
        'sent_at',
        'response_at',
        'next_reminder_date',
        'reminder_count',
        'response_notes',
        'priority',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'response_at' => 'datetime',
        'next_reminder_date' => 'date',
    ];

    // Types de relances
    public const TYPES = [
        'invoice' => 'Facture impayee',
        'proposal' => 'Devis en attente',
        'contract' => 'Contrat',
        'general' => 'Relance generale',
    ];

    // Canaux de communication
    public const CHANNELS = [
        'email' => 'Email',
        'phone' => 'Telephone',
        'sms' => 'SMS',
        'meeting' => 'Reunion',
        'letter' => 'Courrier',
    ];

    // Statuts
    public const STATUSES = [
        'pending' => 'En attente',
        'sent' => 'Envoyee',
        'acknowledged' => 'Accusee',
        'no_response' => 'Sans reponse',
        'resolved' => 'Resolue',
    ];

    // Priorités
    public const PRIORITIES = [
        'low' => 'Basse',
        'normal' => 'Normale',
        'high' => 'Haute',
        'urgent' => 'Urgente',
    ];

    // Relations
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function remindable(): MorphTo
    {
        return $this->morphTo();
    }

    // Accesseurs
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->reminder_type] ?? $this->reminder_type;
    }

    public function getChannelLabelAttribute(): string
    {
        return self::CHANNELS[$this->channel] ?? $this->channel;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->scheduled_at) {
            return false;
        }
        return $this->status === 'pending' && $this->scheduled_at->isPast();
    }

    public function getIsDueTodayAttribute(): bool
    {
        if (!$this->scheduled_at) {
            return false;
        }
        return $this->status === 'pending' && $this->scheduled_at->isToday();
    }

    public function getDaysSinceLastReminderAttribute(): ?int
    {
        if (!$this->sent_at) {
            return null;
        }
        return $this->sent_at->diffInDays(now());
    }

    // Méthodes
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsAcknowledged(?string $notes = null): void
    {
        $this->update([
            'status' => 'acknowledged',
            'response_at' => now(),
            'response_notes' => $notes,
        ]);
    }

    public function markAsNoResponse(): void
    {
        $this->update([
            'status' => 'no_response',
        ]);
    }

    public function markAsResolved(?string $notes = null): void
    {
        $this->update([
            'status' => 'resolved',
            'response_at' => now(),
            'response_notes' => $notes,
        ]);
    }

    public function scheduleNextReminder(int $daysFromNow = 7): self
    {
        return self::create([
            'contact_id' => $this->contact_id,
            'created_by' => auth()->id(),
            'reminder_type' => $this->reminder_type,
            'remindable_type' => $this->remindable_type,
            'remindable_id' => $this->remindable_id,
            'subject' => $this->subject,
            'message' => $this->message,
            'channel' => $this->channel,
            'status' => 'pending',
            'scheduled_at' => now()->addDays($daysFromNow),
            'reminder_count' => $this->reminder_count + 1,
            'priority' => $this->reminder_count >= 2 ? 'high' : $this->priority,
        ]);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeNoResponse($query)
    {
        return $query->where('status', 'no_response');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
            ->where('scheduled_at', '<', now());
    }

    public function scopeDueToday($query)
    {
        return $query->where('status', 'pending')
            ->whereDate('scheduled_at', today());
    }

    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->where('status', 'pending')
            ->where('scheduled_at', '<=', now()->addDays($days))
            ->where('scheduled_at', '>=', now());
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('reminder_type', $type);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeForContact($query, int $contactId)
    {
        return $query->where('contact_id', $contactId);
    }
}
