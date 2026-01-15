<?php

namespace App\Modules\Agenda\Console\Commands;

use App\Modules\Agenda\Services\EventService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEventReminders extends Command
{
    protected $signature = 'events:send-reminders';

    protected $description = 'Envoie les rappels pour les événements à venir';

    public function handle(EventService $eventService): int
    {
        $this->info('Recherche des événements nécessitant un rappel...');

        $events = $eventService->getEventsNeedingReminder();

        if ($events->isEmpty()) {
            $this->info('Aucun rappel à envoyer.');
            return Command::SUCCESS;
        }

        $this->info("Envoi de {$events->count()} rappel(s)...");

        foreach ($events as $event) {
            try {
                $this->sendReminder($event);
                $eventService->markReminderSent($event);
                $this->line(" ✓ Rappel envoyé pour: {$event->title}");
            } catch (\Exception $e) {
                $this->error(" ✗ Erreur pour {$event->title}: {$e->getMessage()}");
                Log::error("Erreur envoi rappel événement {$event->id}: {$e->getMessage()}");
            }
        }

        $this->info('Terminé.');

        return Command::SUCCESS;
    }

    private function sendReminder($event): void
    {
        // Récupérer tous les destinataires
        $recipients = collect();

        // Créateur de l'événement
        if ($event->creator && $event->creator->email) {
            $recipients->push([
                'email' => $event->creator->email,
                'name' => $event->creator->name,
            ]);
        }

        // Participants
        foreach ($event->attendees as $attendee) {
            if ($attendee->email && $attendee->pivot->status !== 'declined') {
                $recipients->push([
                    'email' => $attendee->email,
                    'name' => $attendee->full_name,
                ]);
            }
        }

        // Envoyer les emails
        foreach ($recipients->unique('email') as $recipient) {
            $this->sendReminderEmail($event, $recipient);
        }
    }

    private function sendReminderEmail($event, array $recipient): void
    {
        $subject = "Rappel: {$event->title}";

        $typeLabels = [
            'meeting' => 'Réunion',
            'call' => 'Appel',
            'task' => 'Tâche',
            'reminder' => 'Rappel',
            'other' => 'Événement',
        ];

        $typeLabel = $typeLabels[$event->type] ?? 'Événement';

        $body = "Bonjour {$recipient['name']},\n\n";
        $body .= "Ceci est un rappel pour votre événement à venir:\n\n";
        $body .= "📅 {$typeLabel}: {$event->title}\n";

        if ($event->all_day) {
            $body .= "📆 Date: {$event->start_at->format('d/m/Y')} (Toute la journée)\n";
        } else {
            $body .= "📆 Date: {$event->start_at->format('d/m/Y')}\n";
            $body .= "🕐 Heure: {$event->start_at->format('H:i')} - {$event->end_at->format('H:i')}\n";
        }

        if ($event->location) {
            $body .= "📍 Lieu: {$event->location}\n";
        }

        if ($event->description) {
            $body .= "\n📝 Description:\n{$event->description}\n";
        }

        if ($event->project) {
            $body .= "\n📁 Projet: {$event->project->name}\n";
        }

        $body .= "\n---\n";
        $body .= "Ce rappel a été envoyé automatiquement par votre ERP.\n";

        // Envoi via Mail (configuration requise)
        try {
            Mail::raw($body, function ($message) use ($recipient, $subject) {
                $message->to($recipient['email'], $recipient['name'])
                    ->subject($subject);
            });
        } catch (\Exception $e) {
            // Log silencieusement si le mail échoue mais ne bloque pas
            Log::warning("Impossible d'envoyer le rappel à {$recipient['email']}: {$e->getMessage()}");
        }
    }
}
