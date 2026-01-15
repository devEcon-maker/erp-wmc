<?php

namespace App\Modules\Agenda\Models;

use App\Models\User;
use App\Modules\CRM\Models\Contact;
use App\Modules\HR\Models\Employee;
use App\Modules\Productivity\Models\Project;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_at',
        'end_at',
        'all_day',
        'location',
        'color',
        'type',
        'is_recurring',
        'recurrence_rule',
        'recurrence_end',
        'parent_event_id',
        'reminder_minutes',
        'reminder_sent',
        'created_by',
        'project_id',
        'contact_id',
        'visibility',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'all_day' => 'boolean',
        'is_recurring' => 'boolean',
        'recurrence_end' => 'date',
        'reminder_sent' => 'boolean',
    ];

    // Relations
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function parentEvent(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'parent_event_id');
    }

    public function childEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'parent_event_id');
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'event_attendees')
            ->withPivot(['status', 'is_organizer'])
            ->withTimestamps();
    }

    // Accessors
    public function getDurationMinutesAttribute(): int
    {
        return $this->start_at->diffInMinutes($this->end_at);
    }

    public function getDurationHoursAttribute(): float
    {
        return round($this->duration_minutes / 60, 1);
    }

    public function getIsAllDayAttribute(): bool
    {
        return $this->all_day;
    }

    public function getIsPastAttribute(): bool
    {
        return $this->end_at->isPast();
    }

    public function getIsOngoingAttribute(): bool
    {
        return now()->between($this->start_at, $this->end_at);
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->start_at->isFuture();
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'meeting' => 'groups',
            'call' => 'call',
            'task' => 'task',
            'reminder' => 'notifications',
            default => 'event',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'meeting' => 'Réunion',
            'call' => 'Appel',
            'task' => 'Tâche',
            'reminder' => 'Rappel',
            default => 'Autre',
        };
    }

    public function getReminderTimeAttribute(): ?Carbon
    {
        if (!$this->reminder_minutes) {
            return null;
        }

        return $this->start_at->copy()->subMinutes($this->reminder_minutes);
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('start_at', '>=', now())->orderBy('start_at');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('start_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('start_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('start_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->where(function ($q) use ($start, $end) {
            $q->whereBetween('start_at', [$start, $end])
                ->orWhereBetween('end_at', [$start, $end])
                ->orWhere(function ($q2) use ($start, $end) {
                    $q2->where('start_at', '<=', $start)
                        ->where('end_at', '>=', $end);
                });
        });
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('created_by', $userId)
                ->orWhere('visibility', 'public')
                ->orWhereHas('attendees', function ($q2) use ($userId) {
                    $q2->whereHas('user', function ($q3) use ($userId) {
                        $q3->where('id', $userId);
                    });
                });
        });
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeNeedingReminder($query)
    {
        return $query->whereNotNull('reminder_minutes')
            ->where('reminder_sent', false)
            ->whereRaw('DATE_SUB(start_at, INTERVAL reminder_minutes MINUTE) <= NOW()')
            ->where('start_at', '>', now());
    }

    // Methods
    public function toFullCalendarEvent(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->all_day
                ? $this->start_at->format('Y-m-d')
                : $this->start_at->toIso8601String(),
            'end' => $this->all_day
                ? $this->end_at->addDay()->format('Y-m-d')
                : $this->end_at->toIso8601String(),
            'allDay' => $this->all_day,
            'backgroundColor' => $this->color,
            'borderColor' => $this->color,
            'extendedProps' => [
                'type' => $this->type,
                'location' => $this->location,
                'description' => $this->description,
                'project_id' => $this->project_id,
                'contact_id' => $this->contact_id,
            ],
        ];
    }

    public function generateOccurrences(Carbon $until): array
    {
        if (!$this->is_recurring || !$this->recurrence_rule) {
            return [$this];
        }

        $occurrences = [];
        $rule = $this->parseRecurrenceRule();
        $current = $this->start_at->copy();
        $endDate = $this->recurrence_end ? min($until, Carbon::parse($this->recurrence_end)) : $until;
        $duration = $this->duration_minutes;

        while ($current <= $endDate) {
            $occurrence = $this->replicate();
            $occurrence->start_at = $current->copy();
            $occurrence->end_at = $current->copy()->addMinutes($duration);
            $occurrence->id = $this->id;
            $occurrences[] = $occurrence;

            $current = $this->getNextOccurrence($current, $rule);
            if (!$current || $current > $endDate) {
                break;
            }
        }

        return $occurrences;
    }

    private function parseRecurrenceRule(): array
    {
        $rule = [];
        $parts = explode(';', $this->recurrence_rule);

        foreach ($parts as $part) {
            $keyValue = explode('=', $part);
            if (count($keyValue) === 2) {
                $rule[$keyValue[0]] = $keyValue[1];
            }
        }

        return $rule;
    }

    private function getNextOccurrence(Carbon $current, array $rule): ?Carbon
    {
        $freq = $rule['FREQ'] ?? 'DAILY';
        $interval = (int)($rule['INTERVAL'] ?? 1);

        return match($freq) {
            'DAILY' => $current->copy()->addDays($interval),
            'WEEKLY' => $current->copy()->addWeeks($interval),
            'MONTHLY' => $current->copy()->addMonths($interval),
            'YEARLY' => $current->copy()->addYears($interval),
            default => null,
        };
    }
}
