<?php

namespace App\Modules\CRM\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Modules\CRM\Models\Reminder;
use App\Modules\CRM\Models\Contact;
use App\Modules\CRM\Models\Proposal;
use App\Modules\CRM\Models\Contract;
use App\Modules\Finance\Models\Invoice;

class ReminderList extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $type = '';
    public $priority = '';
    public $channel = '';
    public $dateFilter = '';

    // Modal création/édition
    public $showModal = false;
    public $editingReminder = null;

    // Formulaire
    public $contact_id = '';
    public $reminder_type = 'general';
    public $remindable_type = '';
    public $remindable_id = '';
    public $subject = '';
    public $message = '';
    public $form_channel = 'email';
    public $scheduled_at = '';
    public $form_priority = 'normal';

    // Modal réponse
    public $showResponseModal = false;
    public $respondingReminder = null;
    public $response_notes = '';
    public $response_status = 'acknowledged';

    protected $queryString = ['search', 'status', 'type', 'priority', 'channel', 'dateFilter'];

    protected function rules()
    {
        return [
            'contact_id' => 'required|exists:contacts,id',
            'reminder_type' => 'required|in:invoice,proposal,contract,general',
            'remindable_type' => 'nullable|string',
            'remindable_id' => 'nullable|integer',
            'subject' => 'required|string|max:255',
            'message' => 'nullable|string',
            'form_channel' => 'required|in:email,phone,sms,meeting,letter',
            'scheduled_at' => 'required|date',
            'form_priority' => 'required|in:low,normal,high,urgent',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getRemindersProperty()
    {
        return Reminder::query()
            ->with(['contact', 'creator', 'remindable'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('subject', 'like', '%' . $this->search . '%')
                        ->orWhereHas('contact', function ($cq) {
                            $cq->where('first_name', 'like', '%' . $this->search . '%')
                                ->orWhere('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('company_name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->type, fn($q) => $q->where('reminder_type', $this->type))
            ->when($this->priority, fn($q) => $q->where('priority', $this->priority))
            ->when($this->channel, fn($q) => $q->where('channel', $this->channel))
            ->when($this->dateFilter === 'overdue', fn($q) => $q->overdue())
            ->when($this->dateFilter === 'today', fn($q) => $q->dueToday())
            ->when($this->dateFilter === 'week', fn($q) => $q->dueSoon(7))
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
            ->orderBy('scheduled_at', 'asc')
            ->paginate(15);
    }

    public function getStatsProperty()
    {
        return [
            'total' => Reminder::count(),
            'pending' => Reminder::pending()->count(),
            'overdue' => Reminder::overdue()->count(),
            'due_today' => Reminder::dueToday()->count(),
            'sent' => Reminder::sent()->count(),
            'no_response' => Reminder::noResponse()->count(),
            'resolved' => Reminder::where('status', 'resolved')->count(),
            'urgent' => Reminder::where('priority', 'urgent')->pending()->count(),
        ];
    }

    public function getContactsProperty()
    {
        return Contact::orderBy('company_name')
            ->orderBy('last_name')
            ->get();
    }

    public function getRemindableOptionsProperty()
    {
        if (!$this->contact_id || $this->reminder_type === 'general') {
            return [];
        }

        return match ($this->reminder_type) {
            'invoice' => Invoice::where('contact_id', $this->contact_id)
                ->whereIn('status', ['draft', 'sent', 'partial'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($i) => ['id' => $i->id, 'label' => "{$i->reference} - {$i->formatted_total_ttc}"]),
            'proposal' => Proposal::where('contact_id', $this->contact_id)
                ->whereIn('status', ['draft', 'sent'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($p) => ['id' => $p->id, 'label' => "{$p->reference} - {$p->formatted_total_ttc}"]),
            'contract' => Contract::where('contact_id', $this->contact_id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($c) => ['id' => $c->id, 'label' => "{$c->reference} - {$c->category_label}"]),
            default => [],
        };
    }

    public function updatedReminderType()
    {
        $this->remindable_type = '';
        $this->remindable_id = '';
    }

    public function updatedContactId()
    {
        $this->remindable_id = '';
    }

    public function create()
    {
        $this->resetForm();
        $this->scheduled_at = now()->addDay()->format('Y-m-d\TH:i');
        $this->showModal = true;
    }

    public function edit(Reminder $reminder)
    {
        $this->editingReminder = $reminder;
        $this->contact_id = $reminder->contact_id;
        $this->reminder_type = $reminder->reminder_type;
        $this->remindable_type = $reminder->remindable_type;
        $this->remindable_id = $reminder->remindable_id;
        $this->subject = $reminder->subject;
        $this->message = $reminder->message;
        $this->form_channel = $reminder->channel;
        $this->scheduled_at = $reminder->scheduled_at?->format('Y-m-d\TH:i');
        $this->form_priority = $reminder->priority;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $remindableType = match ($this->reminder_type) {
            'invoice' => Invoice::class,
            'proposal' => Proposal::class,
            'contract' => Contract::class,
            default => null,
        };

        $data = [
            'contact_id' => $this->contact_id,
            'reminder_type' => $this->reminder_type,
            'remindable_type' => $this->remindable_id ? $remindableType : null,
            'remindable_id' => $this->remindable_id ?: null,
            'subject' => $this->subject,
            'message' => $this->message,
            'channel' => $this->form_channel,
            'scheduled_at' => $this->scheduled_at,
            'priority' => $this->form_priority,
            'status' => 'pending',
        ];

        if ($this->editingReminder) {
            $this->editingReminder->update($data);
            $message = "Relance mise à jour avec succès.";
        } else {
            $data['created_by'] = auth()->id();
            $data['reminder_count'] = 1;
            Reminder::create($data);
            $message = "Relance créée avec succès.";
        }

        session()->flash('success', $message);
        $this->closeModal();
    }

    public function markAsSent(Reminder $reminder)
    {
        $reminder->markAsSent();
        session()->flash('success', "Relance marquée comme envoyée.");
    }

    public function openResponseModal(Reminder $reminder)
    {
        $this->respondingReminder = $reminder;
        $this->response_notes = '';
        $this->response_status = 'acknowledged';
        $this->showResponseModal = true;
    }

    public function saveResponse()
    {
        if (!$this->respondingReminder) {
            return;
        }

        match ($this->response_status) {
            'acknowledged' => $this->respondingReminder->markAsAcknowledged($this->response_notes),
            'no_response' => $this->respondingReminder->markAsNoResponse(),
            'resolved' => $this->respondingReminder->markAsResolved($this->response_notes),
            default => null,
        };

        $statusLabel = Reminder::STATUSES[$this->response_status] ?? $this->response_status;
        session()->flash('success', "Relance mise à jour: {$statusLabel}");

        $this->showResponseModal = false;
        $this->respondingReminder = null;
        $this->response_notes = '';
    }

    public function scheduleFollowUp(Reminder $reminder, int $days = 7)
    {
        $newReminder = $reminder->scheduleNextReminder($days);
        session()->flash('success', "Nouvelle relance programmée pour le " . $newReminder->scheduled_at->format('d/m/Y') . ".");
    }

    public function delete(Reminder $reminder)
    {
        $reminder->delete();
        session()->flash('success', "Relance supprimée.");
    }

    // Email Modal
    public $showEmailModal = false;
    public $sendingReminderId = null;
    public $selectedSmtpId = null;
    public $emailTo = '';
    public $emailSubject = '';
    public $emailMessage = '';

    public function openEmailModal(Reminder $reminder)
    {
        $this->sendingReminderId = $reminder->id;
        $this->emailTo = $reminder->contact->email ?? '';
        $this->emailSubject = $reminder->subject;

        // Template de message par defaut
        $this->emailMessage = "Bonjour,\n\n" . $reminder->message . "\n\nCordialement,\n" . config('app.name');

        // Selectionner la config SMTP par defaut
        $defaultSmtp = \App\Modules\Core\Models\SmtpConfiguration::where('is_default', true)->where('is_active', true)->first();
        $this->selectedSmtpId = $defaultSmtp?->id;

        $this->showEmailModal = true;
    }

    public function sendReminderEmail()
    {
        $this->validate([
            'emailTo' => 'required|email',
            'emailSubject' => 'required|string|max:255',
            'emailMessage' => 'required|string',
            'selectedSmtpId' => 'required|exists:smtp_configurations,id',
        ]);

        try {
            $reminder = Reminder::findOrFail($this->sendingReminderId);
            $smtp = \App\Modules\Core\Models\SmtpConfiguration::findOrFail($this->selectedSmtpId);

            // Appliquer la config SMTP
            $smtp->applyToMailer();

            // Envoyer l'email
            \Illuminate\Support\Facades\Mail::raw($this->emailMessage, function ($message) use ($reminder) {
                $message->to($this->emailTo)
                    ->subject($this->emailSubject);
            });

            // Marquer comme envoyee
            $reminder->markAsSent();

            $this->showEmailModal = false;
            $this->sendingReminderId = null;
            session()->flash('success', "Relance envoyée avec succès à {$this->emailTo}");

        } catch (\Exception $e) {
            session()->flash('error', "Erreur lors de l'envoi: " . $e->getMessage());
        }
    }

    public function getSmtpConfigurationsProperty()
    {
        return \App\Modules\Core\Models\SmtpConfiguration::where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showResponseModal = false;
        $this->showEmailModal = false;
        $this->resetForm();
    }

    protected function resetForm()
    {
        $this->editingReminder = null;
        $this->respondingReminder = null;
        $this->contact_id = '';
        $this->reminder_type = 'general';
        $this->remindable_type = '';
        $this->remindable_id = '';
        $this->subject = '';
        $this->message = '';
        $this->form_channel = 'email';
        $this->scheduled_at = '';
        $this->form_priority = 'normal';
        $this->response_notes = '';

        // Email
        $this->sendingReminderId = null;
        $this->selectedSmtpId = null;
        $this->emailTo = '';
        $this->emailSubject = '';
        $this->emailMessage = '';

        $this->resetValidation();
    }

    public function render()
    {
        return view('crm::livewire.reminder-list', [
            'reminders' => $this->reminders,
            'stats' => $this->stats,
            'contacts' => $this->contacts,
            'remindableOptions' => $this->remindableOptions,
            'types' => Reminder::TYPES,
            'channels' => Reminder::CHANNELS,
            'statuses' => Reminder::STATUSES,
            'priorities' => Reminder::PRIORITIES,
            'smtpConfigurations' => $this->smtpConfigurations,
        ])->layout('layouts.app');
    }
}
