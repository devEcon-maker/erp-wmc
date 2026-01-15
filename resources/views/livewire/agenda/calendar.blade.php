<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white">Agenda</h2>
            <p class="text-text-secondary text-sm">Gérez vos événements et rendez-vous.</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="openEventModal"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/80 flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Nouvel événement
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">Ce mois</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
                </div>
                <span class="material-symbols-outlined text-primary">event</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">Aujourd'hui</p>
                    <p class="text-2xl font-bold text-blue-400">{{ $stats['today'] }}</p>
                </div>
                <span class="material-symbols-outlined text-blue-400">today</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">Réunions</p>
                    <p class="text-2xl font-bold text-green-400">{{ $stats['meetings'] }}</p>
                </div>
                <span class="material-symbols-outlined text-green-400">groups</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-sm">Appels</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ $stats['calls'] }}</p>
                </div>
                <span class="material-symbols-outlined text-yellow-400">call</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Calendar -->
        <div class="lg:col-span-3 bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
            <!-- Navigation -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-2">
                    <button wire:click="previousMonth" class="p-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg">
                        <span class="material-symbols-outlined">chevron_left</span>
                    </button>
                    <h3 class="text-xl font-semibold text-white min-w-[200px] text-center">
                        {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->translatedFormat('F Y') }}
                    </h3>
                    <button wire:click="nextMonth" class="p-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg">
                        <span class="material-symbols-outlined">chevron_right</span>
                    </button>
                </div>
                <button wire:click="goToToday" class="px-4 py-2 text-sm text-primary hover:bg-primary/10 rounded-lg">
                    Aujourd'hui
                </button>
            </div>

            <!-- FullCalendar Container -->
            <div id="calendar"
                x-data="{
                    calendar: null,
                    init() {
                        this.waitForFullCalendar();
                    },
                    waitForFullCalendar() {
                        if (typeof FullCalendar !== 'undefined') {
                            this.initCalendar();
                            Livewire.on('refreshCalendar', () => {
                                if (this.calendar) {
                                    this.calendar.refetchEvents();
                                }
                            });
                        } else {
                            setTimeout(() => this.waitForFullCalendar(), 50);
                        }
                    },
                    initCalendar() {
                        const calendarEl = document.getElementById('calendar');
                        this.calendar = new FullCalendar.Calendar(calendarEl, {
                            initialView: 'dayGridMonth',
                            locale: 'fr',
                            headerToolbar: false,
                            events: {{ Js::from($events) }},
                            editable: true,
                            selectable: true,
                            selectMirror: true,
                            dayMaxEvents: 3,
                            height: 'auto',
                            eventClick: (info) => {
                                $wire.showEvent(info.event.id);
                            },
                            dateClick: (info) => {
                                $wire.createEventOnDate(info.dateStr);
                            },
                            eventDrop: (info) => {
                                $wire.moveEvent(
                                    info.event.id,
                                    info.event.start.toISOString(),
                                    info.event.end ? info.event.end.toISOString() : null
                                );
                            },
                            eventResize: (info) => {
                                $wire.resizeEvent(
                                    info.event.id,
                                    info.event.end.toISOString()
                                );
                            },
                            eventDidMount: (info) => {
                                info.el.style.cursor = 'pointer';
                            }
                        });
                        this.calendar.render();
                    }
                }"
                x-init="init()"
                wire:ignore
                class="fc-dark-theme">
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Today's Events -->
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">today</span>
                    Aujourd'hui
                </h3>

                @if($todaysEvents->count() > 0)
                    <div class="space-y-2">
                        @foreach($todaysEvents as $event)
                            <button wire:click="showEvent({{ $event->id }})"
                                class="w-full text-left p-3 rounded-lg hover:bg-surface-highlight transition-colors">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $event->color }}"></span>
                                    <span class="text-sm font-medium text-white">{{ $event->title }}</span>
                                </div>
                                <div class="text-xs text-text-secondary pl-4">
                                    @if($event->all_day)
                                        Toute la journée
                                    @else
                                        {{ $event->start_at->format('H:i') }} - {{ $event->end_at->format('H:i') }}
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-text-secondary text-sm">
                        <span class="material-symbols-outlined text-2xl mb-2 opacity-50">event_busy</span>
                        <p>Aucun événement</p>
                    </div>
                @endif
            </div>

            <!-- Quick Add -->
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">bolt</span>
                    Actions rapides
                </h3>
                <div class="space-y-2">
                    <button wire:click="quickMeeting"
                        class="w-full p-2 text-left text-sm text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-blue-400">groups</span>
                        Nouvelle réunion
                    </button>
                    <button wire:click="quickCall"
                        class="w-full p-2 text-left text-sm text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-green-400">call</span>
                        Nouvel appel
                    </button>
                    <button wire:click="quickReminder"
                        class="w-full p-2 text-left text-sm text-text-secondary hover:text-white hover:bg-surface-highlight rounded-lg flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-yellow-400">notifications</span>
                        Nouveau rappel
                    </button>
                </div>
            </div>

            <!-- Legend -->
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">palette</span>
                    Légende
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-[#E76F51]"></span>
                        <span class="text-text-secondary">Par défaut</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                        <span class="text-text-secondary">Réunion</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                        <span class="text-text-secondary">Appel</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                        <span class="text-text-secondary">Rappel</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Modal -->
    @if($showEventModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" wire:click.self="closeEventModal">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl w-full max-w-3xl max-h-[90vh] flex flex-col">
                <!-- Header fixe -->
                <div class="p-4 border-b border-[#3a2e24] flex items-center justify-between flex-shrink-0">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">{{ $editingEvent ? 'edit_calendar' : 'event' }}</span>
                        {{ $editingEvent ? 'Modifier l\'evenement' : 'Nouvel evenement' }}
                    </h3>
                    <button wire:click="closeEventModal" class="text-text-secondary hover:text-white p-1">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Contenu scrollable -->
                <div class="p-4 overflow-y-auto flex-1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Colonne gauche -->
                        <div class="space-y-3">
                            <x-ui.input
                                label="Titre *"
                                wire:model="eventForm.title"
                                placeholder="Titre de l'evenement"
                                :error="$errors->first('eventForm.title')"
                            />

                            <div class="grid grid-cols-2 gap-3">
                                <x-ui.select label="Type" wire:model="eventForm.type">
                                    <option value="meeting">Reunion</option>
                                    <option value="call">Appel</option>
                                    <option value="task">Tache</option>
                                    <option value="reminder">Rappel</option>
                                    <option value="other">Autre</option>
                                </x-ui.select>

                                <div>
                                    <label class="block text-sm font-medium text-text-secondary mb-1">Couleur</label>
                                    <input type="color" wire:model="eventForm.color"
                                        class="w-full h-[42px] rounded-lg border border-[#3a2e24] bg-surface-highlight cursor-pointer">
                                </div>
                            </div>

                            <label class="flex items-center gap-2 cursor-pointer py-1">
                                <input type="checkbox" wire:model.live="eventForm.all_day"
                                    class="w-4 h-4 rounded bg-surface-highlight border-[#3a2e24] text-primary focus:ring-primary">
                                <span class="text-white text-sm">Toute la journee</span>
                            </label>

                            <!-- Dates sur une ligne -->
                            <div class="grid grid-cols-2 gap-3">
                                <x-ui.input
                                    type="date"
                                    label="Debut *"
                                    wire:model="eventForm.start_at"
                                    :error="$errors->first('eventForm.start_at')"
                                />
                                <x-ui.input
                                    type="date"
                                    label="Fin *"
                                    wire:model="eventForm.end_at"
                                    :error="$errors->first('eventForm.end_at')"
                                />
                            </div>

                            @if(!$eventForm['all_day'])
                                <div class="grid grid-cols-2 gap-3">
                                    <x-ui.input
                                        type="time"
                                        label="Heure debut"
                                        wire:model="eventForm.start_time"
                                    />
                                    <x-ui.input
                                        type="time"
                                        label="Heure fin"
                                        wire:model="eventForm.end_time"
                                    />
                                </div>
                            @endif

                            <x-ui.input
                                label="Lieu"
                                wire:model="eventForm.location"
                                placeholder="Adresse ou lien visio"
                                icon="location_on"
                            />

                            <x-ui.textarea
                                label="Description"
                                wire:model="eventForm.description"
                                placeholder="Description..."
                                rows="2"
                            />
                        </div>

                        <!-- Colonne droite -->
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <x-ui.select label="Projet" wire:model="eventForm.project_id">
                                    <option value="">Aucun</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </x-ui.select>

                                <x-ui.select label="Contact" wire:model="eventForm.contact_id">
                                    <option value="">Aucun</option>
                                    @foreach($contacts as $contact)
                                        <option value="{{ $contact->id }}">{{ $contact->company_name }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <x-ui.select label="Rappel" wire:model="eventForm.reminder_minutes">
                                    <option value="">Aucun</option>
                                    <option value="5">5 min avant</option>
                                    <option value="15">15 min avant</option>
                                    <option value="30">30 min avant</option>
                                    <option value="60">1h avant</option>
                                    <option value="1440">1 jour avant</option>
                                </x-ui.select>

                                <x-ui.select label="Visibilite" wire:model="eventForm.visibility">
                                    <option value="team">Equipe</option>
                                    <option value="public">Public</option>
                                    <option value="private">Prive</option>
                                </x-ui.select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-text-secondary mb-1">Participants</label>
                                <div class="grid grid-cols-2 gap-1 max-h-28 overflow-y-auto p-2 bg-surface-highlight rounded-lg border border-[#3a2e24]">
                                    @foreach($employees as $employee)
                                        <label class="flex items-center gap-2 cursor-pointer p-1 hover:bg-surface-dark rounded text-sm">
                                            <input type="checkbox" wire:model="selectedAttendees" value="{{ $employee->id }}"
                                                class="w-3.5 h-3.5 rounded bg-surface-highlight border-[#3a2e24] text-primary focus:ring-primary">
                                            <span class="text-white truncate">{{ $employee->full_name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <label class="flex items-center gap-2 cursor-pointer py-1">
                                <input type="checkbox" wire:model.live="eventForm.is_recurring"
                                    class="w-4 h-4 rounded bg-surface-highlight border-[#3a2e24] text-primary focus:ring-primary">
                                <span class="text-white text-sm">Evenement recurrent</span>
                            </label>

                            @if($eventForm['is_recurring'])
                                <div class="grid grid-cols-2 gap-3 p-3 bg-surface-highlight rounded-lg border border-[#3a2e24]">
                                    <x-ui.select label="Frequence" wire:model="eventForm.recurrence_rule">
                                        <option value="">Selectionner...</option>
                                        <option value="FREQ=DAILY">Quotidien</option>
                                        <option value="FREQ=WEEKLY">Hebdo</option>
                                        <option value="FREQ=WEEKLY;BYDAY=MO,TU,WE,TH,FR">Jours ouvres</option>
                                        <option value="FREQ=MONTHLY">Mensuel</option>
                                        <option value="FREQ=YEARLY">Annuel</option>
                                    </x-ui.select>
                                    <x-ui.input
                                        type="date"
                                        label="Jusqu'au"
                                        wire:model="eventForm.recurrence_end"
                                    />
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer fixe -->
                <div class="flex justify-end gap-3 p-4 border-t border-[#3a2e24] flex-shrink-0">
                    <x-ui.button type="secondary" wire:click="closeEventModal">Annuler</x-ui.button>
                    <x-ui.button wire:click="saveEvent">
                        <span class="material-symbols-outlined text-[18px] mr-1">{{ $editingEvent ? 'save' : 'add' }}</span>
                        {{ $editingEvent ? 'Mettre a jour' : 'Creer' }}
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif

    <!-- Event Details Modal -->
    @if($showEventDetails && $selectedEvent)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" wire:click.self="closeEventDetails">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-xl w-full max-w-lg">
                <div class="p-6 border-b border-[#3a2e24]" style="background-color: {{ $selectedEvent->color }}20">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="material-symbols-outlined" style="color: {{ $selectedEvent->color }}">
                                    {{ $selectedEvent->type_icon }}
                                </span>
                                <span class="text-sm px-2 py-0.5 rounded-full" style="background-color: {{ $selectedEvent->color }}40; color: {{ $selectedEvent->color }}">
                                    {{ $selectedEvent->type_label }}
                                </span>
                            </div>
                            <h3 class="text-xl font-semibold text-white">{{ $selectedEvent->title }}</h3>
                        </div>
                        <button wire:click="closeEventDetails" class="text-text-secondary hover:text-white">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-3 text-text-secondary">
                        <span class="material-symbols-outlined">schedule</span>
                        <div>
                            @if($selectedEvent->all_day)
                                <p class="text-white">{{ $selectedEvent->start_at->translatedFormat('l d F Y') }}</p>
                                <p class="text-sm">Toute la journée</p>
                            @else
                                <p class="text-white">{{ $selectedEvent->start_at->translatedFormat('l d F Y') }}</p>
                                <p class="text-sm">{{ $selectedEvent->start_at->format('H:i') }} - {{ $selectedEvent->end_at->format('H:i') }}</p>
                            @endif
                        </div>
                    </div>

                    @if($selectedEvent->location)
                        <div class="flex items-center gap-3 text-text-secondary">
                            <span class="material-symbols-outlined">location_on</span>
                            <span class="text-white">{{ $selectedEvent->location }}</span>
                        </div>
                    @endif

                    @if($selectedEvent->description)
                        <div class="flex items-start gap-3 text-text-secondary">
                            <span class="material-symbols-outlined">description</span>
                            <p class="text-white whitespace-pre-line">{{ $selectedEvent->description }}</p>
                        </div>
                    @endif

                    @if($selectedEvent->project)
                        <div class="flex items-center gap-3 text-text-secondary">
                            <span class="material-symbols-outlined">folder</span>
                            <a href="{{ route('productivity.projects.show', $selectedEvent->project) }}"
                                class="text-primary hover:text-primary/80">
                                {{ $selectedEvent->project->name }}
                            </a>
                        </div>
                    @endif

                    @if($selectedEvent->contact)
                        <div class="flex items-center gap-3 text-text-secondary">
                            <span class="material-symbols-outlined">business</span>
                            <a href="{{ route('crm.contacts.show', $selectedEvent->contact) }}"
                                class="text-primary hover:text-primary/80">
                                {{ $selectedEvent->contact->company_name }}
                            </a>
                        </div>
                    @endif

                    @if($selectedEvent->attendees->count() > 0)
                        <div class="pt-4 border-t border-[#3a2e24]">
                            <p class="text-sm text-text-secondary mb-2">Participants ({{ $selectedEvent->attendees->count() }})</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($selectedEvent->attendees as $attendee)
                                    <div class="flex items-center gap-2 px-2 py-1 bg-surface-highlight rounded-lg">
                                        <div class="w-6 h-6 rounded-full bg-primary/20 flex items-center justify-center text-xs font-medium text-primary">
                                            {{ substr($attendee->first_name, 0, 1) }}{{ substr($attendee->last_name, 0, 1) }}
                                        </div>
                                        <span class="text-sm text-white">{{ $attendee->full_name }}</span>
                                        <span class="material-symbols-outlined text-[14px] {{ $attendee->pivot->status_color }}">
                                            {{ $attendee->pivot->status === 'accepted' ? 'check_circle' : ($attendee->pivot->status === 'declined' ? 'cancel' : 'help') }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- RSVP Buttons -->
                    @php
                        $user = auth()->user();
                        $isAttendee = $selectedEvent->attendees->contains('id', $user->employee?->id);
                    @endphp
                    @if($isAttendee)
                        <div class="pt-4 border-t border-[#3a2e24]">
                            <p class="text-sm text-text-secondary mb-2">Votre réponse</p>
                            <div class="flex gap-2">
                                <button wire:click="respondToEvent('accepted')"
                                    class="px-3 py-1 text-sm rounded-lg bg-green-500/20 text-green-400 hover:bg-green-500/30">
                                    Accepter
                                </button>
                                <button wire:click="respondToEvent('tentative')"
                                    class="px-3 py-1 text-sm rounded-lg bg-yellow-500/20 text-yellow-400 hover:bg-yellow-500/30">
                                    Peut-être
                                </button>
                                <button wire:click="respondToEvent('declined')"
                                    class="px-3 py-1 text-sm rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30">
                                    Refuser
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex justify-between gap-3 p-6 border-t border-[#3a2e24]">
                    <button wire:click="deleteEvent" wire:confirm="Supprimer cet événement ?"
                        class="px-4 py-2 text-sm text-red-400 hover:bg-red-500/10 rounded-lg flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                        Supprimer
                    </button>
                    <div class="flex gap-2">
                        <x-ui.button type="secondary" wire:click="closeEventDetails">Fermer</x-ui.button>
                        <x-ui.button wire:click="editEvent({{ $selectedEvent->id }})">Modifier</x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<style>
    .fc-dark-theme {
        --fc-border-color: #3a2e24;
        --fc-button-bg-color: #2a2420;
        --fc-button-border-color: #3a2e24;
        --fc-button-hover-bg-color: #3a2e24;
        --fc-button-hover-border-color: #4a3e34;
        --fc-button-active-bg-color: #E76F51;
        --fc-today-bg-color: rgba(231, 111, 81, 0.1);
        --fc-page-bg-color: transparent;
        --fc-neutral-bg-color: #2a2420;
        --fc-list-event-hover-bg-color: #3a2e24;
    }
    .fc-dark-theme .fc-daygrid-day-number,
    .fc-dark-theme .fc-col-header-cell-cushion {
        color: #fff;
    }
    .fc-dark-theme .fc-daygrid-day.fc-day-other .fc-daygrid-day-number {
        color: #666;
    }
    .fc-dark-theme .fc-event {
        cursor: pointer;
        border-radius: 4px;
        padding: 2px 4px;
    }
    .fc-dark-theme .fc-daygrid-more-link {
        color: #E76F51;
    }
</style>
@endpush
