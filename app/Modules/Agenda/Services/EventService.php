<?php

namespace App\Modules\Agenda\Services;

use App\Modules\Agenda\Models\Event;
use App\Modules\Agenda\Models\EventAttendee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EventService
{
    public function create(array $data, array $attendeeIds = []): Event
    {
        $data['created_by'] = Auth::id();

        // Handle all-day events
        if ($data['all_day'] ?? false) {
            $data['start_at'] = Carbon::parse($data['start_at'])->startOfDay();
            $data['end_at'] = Carbon::parse($data['end_at'] ?? $data['start_at'])->endOfDay();
        }

        $event = Event::create($data);

        // Add attendees
        if (!empty($attendeeIds)) {
            $this->syncAttendees($event, $attendeeIds);
        }

        return $event;
    }

    public function update(Event $event, array $data, array $attendeeIds = []): Event
    {
        // Handle all-day events
        if ($data['all_day'] ?? false) {
            $data['start_at'] = Carbon::parse($data['start_at'])->startOfDay();
            $data['end_at'] = Carbon::parse($data['end_at'] ?? $data['start_at'])->endOfDay();
        }

        $event->update($data);

        // Sync attendees
        if (isset($attendeeIds)) {
            $this->syncAttendees($event, $attendeeIds);
        }

        return $event->fresh();
    }

    public function delete(Event $event): bool
    {
        // Delete child events (recurring occurrences)
        $event->childEvents()->delete();

        return $event->delete();
    }

    public function syncAttendees(Event $event, array $attendeeIds): void
    {
        $currentAttendees = $event->attendees()->pluck('employee_id')->toArray();
        $newAttendees = array_diff($attendeeIds, $currentAttendees);
        $removedAttendees = array_diff($currentAttendees, $attendeeIds);

        // Remove old attendees
        EventAttendee::where('event_id', $event->id)
            ->whereIn('employee_id', $removedAttendees)
            ->delete();

        // Add new attendees
        foreach ($newAttendees as $employeeId) {
            EventAttendee::create([
                'event_id' => $event->id,
                'employee_id' => $employeeId,
                'status' => 'pending',
            ]);
        }
    }

    public function updateAttendeeStatus(Event $event, int $employeeId, string $status): void
    {
        EventAttendee::where('event_id', $event->id)
            ->where('employee_id', $employeeId)
            ->update(['status' => $status]);
    }

    public function getEventsForPeriod(Carbon $start, Carbon $end, ?int $userId = null): array
    {
        $query = Event::with(['attendees', 'project', 'contact'])
            ->betweenDates($start, $end);

        if ($userId) {
            $query->forUser($userId);
        }

        $events = $query->get();
        $result = [];

        foreach ($events as $event) {
            if ($event->is_recurring) {
                $occurrences = $event->generateOccurrences($end);
                foreach ($occurrences as $occurrence) {
                    if ($occurrence->start_at >= $start && $occurrence->start_at <= $end) {
                        $result[] = $occurrence->toFullCalendarEvent();
                    }
                }
            } else {
                $result[] = $event->toFullCalendarEvent();
            }
        }

        return $result;
    }

    public function getUpcomingEvents(int $limit = 10, ?int $userId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Event::with(['attendees', 'project'])
            ->upcoming();

        if ($userId) {
            $query->forUser($userId);
        }

        return $query->limit($limit)->get();
    }

    public function getTodaysEvents(?int $userId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Event::with(['attendees', 'project'])
            ->today()
            ->orderBy('start_at');

        if ($userId) {
            $query->forUser($userId);
        }

        return $query->get();
    }

    public function getEventsNeedingReminder(): \Illuminate\Database\Eloquent\Collection
    {
        return Event::with(['creator', 'attendees.employee'])
            ->needingReminder()
            ->get();
    }

    public function markReminderSent(Event $event): void
    {
        $event->update(['reminder_sent' => true]);
    }

    public function duplicate(Event $event, ?Carbon $newDate = null): Event
    {
        $newEvent = $event->replicate();
        $newEvent->title = $event->title . ' (copie)';
        $newEvent->reminder_sent = false;

        if ($newDate) {
            $duration = $event->duration_minutes;
            $newEvent->start_at = $newDate;
            $newEvent->end_at = $newDate->copy()->addMinutes($duration);
        }

        $newEvent->save();

        // Copy attendees
        foreach ($event->attendees as $attendee) {
            EventAttendee::create([
                'event_id' => $newEvent->id,
                'employee_id' => $attendee->id,
                'status' => 'pending',
            ]);
        }

        return $newEvent;
    }

    public function moveEvent(Event $event, Carbon $newStart, ?Carbon $newEnd = null): Event
    {
        $duration = $event->duration_minutes;

        $event->update([
            'start_at' => $newStart,
            'end_at' => $newEnd ?? $newStart->copy()->addMinutes($duration),
        ]);

        return $event->fresh();
    }

    public function resizeEvent(Event $event, Carbon $newEnd): Event
    {
        $event->update(['end_at' => $newEnd]);

        return $event->fresh();
    }

    public function getEventsByType(string $type, ?Carbon $start = null, ?Carbon $end = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Event::ofType($type);

        if ($start && $end) {
            $query->betweenDates($start, $end);
        }

        return $query->orderBy('start_at')->get();
    }

    public function getCalendarStats(?Carbon $start = null, ?Carbon $end = null): array
    {
        $start = $start ?? now()->startOfMonth();
        $end = $end ?? now()->endOfMonth();

        $events = Event::betweenDates($start, $end)->get();

        return [
            'total' => $events->count(),
            'meetings' => $events->where('type', 'meeting')->count(),
            'calls' => $events->where('type', 'call')->count(),
            'tasks' => $events->where('type', 'task')->count(),
            'reminders' => $events->where('type', 'reminder')->count(),
            'this_week' => $events->filter(fn($e) => $e->start_at->isCurrentWeek())->count(),
            'today' => $events->filter(fn($e) => $e->start_at->isToday())->count(),
        ];
    }
}
