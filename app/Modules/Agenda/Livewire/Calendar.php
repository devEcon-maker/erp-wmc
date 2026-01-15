<?php

namespace App\Modules\Agenda\Livewire;

use App\Modules\Agenda\Models\Event;
use App\Modules\Agenda\Services\EventService;
use App\Modules\HR\Models\Employee;
use App\Modules\Productivity\Models\Project;
use App\Modules\CRM\Models\Contact;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Calendar extends Component
{
    public $currentMonth;
    public $currentYear;

    // Modal
    public $showEventModal = false;
    public $editingEvent = null;
    public $eventForm = [
        'title' => '',
        'description' => '',
        'start_at' => '',
        'start_time' => '',
        'end_at' => '',
        'end_time' => '',
        'all_day' => false,
        'location' => '',
        'color' => '#E76F51',
        'type' => 'meeting',
        'is_recurring' => false,
        'recurrence_rule' => '',
        'recurrence_end' => '',
        'reminder_minutes' => '',
        'project_id' => '',
        'contact_id' => '',
        'visibility' => 'team',
    ];
    public $selectedAttendees = [];

    // View details
    public $showEventDetails = false;
    public $selectedEvent = null;

    protected $listeners = [
        'eventClick' => 'showEvent',
        'dateClick' => 'createEventOnDate',
        'eventDrop' => 'moveEvent',
        'eventResize' => 'resizeEvent',
    ];

    public function mount()
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
    }

    public function getEventsProperty()
    {
        $start = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth()->subWeek();
        $end = Carbon::create($this->currentYear, $this->currentMonth, 1)->endOfMonth()->addWeek();

        $service = app(EventService::class);
        return $service->getEventsForPeriod($start, $end, Auth::id());
    }

    public function previousMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function goToToday()
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
    }

    // Event Modal
    public function openEventModal()
    {
        $this->resetEventForm();
        $this->eventForm['start_at'] = now()->format('Y-m-d');
        $this->eventForm['start_time'] = now()->addHour()->startOfHour()->format('H:i');
        $this->eventForm['end_at'] = now()->format('Y-m-d');
        $this->eventForm['end_time'] = now()->addHours(2)->startOfHour()->format('H:i');
        $this->showEventModal = true;
    }

    public function createEventOnDate($date)
    {
        $this->resetEventForm();
        $this->eventForm['start_at'] = $date;
        $this->eventForm['start_time'] = '09:00';
        $this->eventForm['end_at'] = $date;
        $this->eventForm['end_time'] = '10:00';
        $this->showEventModal = true;
    }

    public function editEvent($eventId)
    {
        $event = Event::with('attendees')->find($eventId);
        if (!$event) return;

        $this->editingEvent = $event;
        $this->eventForm = [
            'title' => $event->title,
            'description' => $event->description ?? '',
            'start_at' => $event->start_at->format('Y-m-d'),
            'start_time' => $event->start_at->format('H:i'),
            'end_at' => $event->end_at->format('Y-m-d'),
            'end_time' => $event->end_at->format('H:i'),
            'all_day' => $event->all_day,
            'location' => $event->location ?? '',
            'color' => $event->color,
            'type' => $event->type,
            'is_recurring' => $event->is_recurring,
            'recurrence_rule' => $event->recurrence_rule ?? '',
            'recurrence_end' => $event->recurrence_end?->format('Y-m-d') ?? '',
            'reminder_minutes' => $event->reminder_minutes ?? '',
            'project_id' => $event->project_id ?? '',
            'contact_id' => $event->contact_id ?? '',
            'visibility' => $event->visibility,
        ];
        $this->selectedAttendees = $event->attendees->pluck('id')->toArray();
        $this->showEventDetails = false;
        $this->showEventModal = true;
    }

    public function closeEventModal()
    {
        $this->showEventModal = false;
        $this->editingEvent = null;
        $this->resetEventForm();
    }

    private function resetEventForm()
    {
        $this->eventForm = [
            'title' => '',
            'description' => '',
            'start_at' => '',
            'start_time' => '',
            'end_at' => '',
            'end_time' => '',
            'all_day' => false,
            'location' => '',
            'color' => '#E76F51',
            'type' => 'meeting',
            'is_recurring' => false,
            'recurrence_rule' => '',
            'recurrence_end' => '',
            'reminder_minutes' => '',
            'project_id' => '',
            'contact_id' => '',
            'visibility' => 'team',
        ];
        $this->selectedAttendees = [];
        $this->editingEvent = null;
    }

    public function saveEvent(EventService $service)
    {
        $this->validate([
            'eventForm.title' => 'required|string|max:255',
            'eventForm.start_at' => 'required|date',
            'eventForm.end_at' => 'required|date|after_or_equal:eventForm.start_at',
            'eventForm.type' => 'required|in:meeting,call,task,reminder,other',
            'eventForm.visibility' => 'required|in:public,private,team',
        ]);

        // Build datetime
        $startAt = $this->eventForm['all_day']
            ? Carbon::parse($this->eventForm['start_at'])->startOfDay()
            : Carbon::parse($this->eventForm['start_at'] . ' ' . $this->eventForm['start_time']);

        $endAt = $this->eventForm['all_day']
            ? Carbon::parse($this->eventForm['end_at'])->endOfDay()
            : Carbon::parse($this->eventForm['end_at'] . ' ' . $this->eventForm['end_time']);

        $data = [
            'title' => $this->eventForm['title'],
            'description' => $this->eventForm['description'] ?: null,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'all_day' => $this->eventForm['all_day'],
            'location' => $this->eventForm['location'] ?: null,
            'color' => $this->eventForm['color'],
            'type' => $this->eventForm['type'],
            'is_recurring' => $this->eventForm['is_recurring'],
            'recurrence_rule' => $this->eventForm['recurrence_rule'] ?: null,
            'recurrence_end' => $this->eventForm['recurrence_end'] ?: null,
            'reminder_minutes' => $this->eventForm['reminder_minutes'] ?: null,
            'project_id' => $this->eventForm['project_id'] ?: null,
            'contact_id' => $this->eventForm['contact_id'] ?: null,
            'visibility' => $this->eventForm['visibility'],
        ];

        if ($this->editingEvent) {
            $service->update($this->editingEvent, $data, $this->selectedAttendees);
            $message = 'Événement mis à jour.';
        } else {
            $service->create($data, $this->selectedAttendees);
            $message = 'Événement créé.';
        }

        $this->closeEventModal();
        $this->dispatch('notify', message: $message, type: 'success');
        $this->dispatch('refreshCalendar');
    }

    // Event Details
    public function showEvent($eventId)
    {
        $this->selectedEvent = Event::with(['attendees', 'creator', 'project', 'contact'])->find($eventId);
        if ($this->selectedEvent) {
            $this->showEventDetails = true;
        }
    }

    public function closeEventDetails()
    {
        $this->showEventDetails = false;
        $this->selectedEvent = null;
    }

    public function deleteEvent(EventService $service)
    {
        if ($this->selectedEvent) {
            $service->delete($this->selectedEvent);
            $this->closeEventDetails();
            $this->dispatch('notify', message: 'Événement supprimé.', type: 'success');
            $this->dispatch('refreshCalendar');
        }
    }

    // Drag & Drop
    public function moveEvent($eventId, $newStart, $newEnd, EventService $service)
    {
        $event = Event::find($eventId);
        if ($event) {
            $service->moveEvent(
                $event,
                Carbon::parse($newStart),
                $newEnd ? Carbon::parse($newEnd) : null
            );
            $this->dispatch('notify', message: 'Événement déplacé.', type: 'success');
        }
    }

    public function resizeEvent($eventId, $newEnd, EventService $service)
    {
        $event = Event::find($eventId);
        if ($event) {
            $service->resizeEvent($event, Carbon::parse($newEnd));
            $this->dispatch('notify', message: 'Événement redimensionné.', type: 'success');
        }
    }

    // RSVP
    public function respondToEvent($status)
    {
        if (!$this->selectedEvent) return;

        $user = Auth::user();
        $employeeId = $user->employee?->id;

        if ($employeeId) {
            $service = app(EventService::class);
            $service->updateAttendeeStatus($this->selectedEvent, $employeeId, $status);
            $this->selectedEvent->refresh();
            $this->dispatch('notify', message: 'Réponse enregistrée.', type: 'success');
        }
    }

    // Properties
    public function getEmployeesProperty()
    {
        return Employee::where('status', 'active')->orderBy('last_name')->get();
    }

    public function getProjectsProperty()
    {
        return Project::whereIn('status', ['active', 'planning'])->orderBy('name')->get();
    }

    public function getContactsProperty()
    {
        return Contact::orderBy('company_name')->get();
    }

    public function getStatsProperty(): array
    {
        $service = app(EventService::class);
        return $service->getCalendarStats(
            Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth(),
            Carbon::create($this->currentYear, $this->currentMonth, 1)->endOfMonth()
        );
    }

    public function getTodaysEventsProperty()
    {
        $service = app(EventService::class);
        return $service->getTodaysEvents(Auth::id());
    }

    public function render()
    {
        return view('livewire.agenda.calendar', [
            'events' => $this->events,
            'employees' => $this->employees,
            'projects' => $this->projects,
            'contacts' => $this->contacts,
            'stats' => $this->stats,
            'todaysEvents' => $this->todaysEvents,
        ])->layout('layouts.app');
    }
}
