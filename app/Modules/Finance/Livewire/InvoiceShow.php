<?php

namespace App\Modules\Finance\Livewire;

use Livewire\Component;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Services\InvoiceService;
use App\Modules\Core\Models\SmtpConfiguration;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceShow extends Component
{
    public Invoice $invoice;

    public $showPaymentModal = false;
    public $paymentAmount;
    public $paymentMethod = 'bank_transfer';
    public $paymentReference;
    public $paymentNotes;

    // Email Modal
    public $showEmailModal = false;
    public $selectedSmtpId = null;
    public $emailTo = '';
    public $emailSubject = '';
    public $emailMessage = '';

    // Delete Modal
    public $showDeleteModal = false;
    public $deletionReason = '';

    public function mount(Invoice $invoice)
    {
        $this->invoice = $invoice->load(['contact', 'lines.product', 'creator', 'payments']);
        $this->paymentAmount = $this->invoice->remaining_balance;
    }

    public function openPaymentModal()
    {
        $this->paymentAmount = $this->invoice->remaining_balance;
        $this->showPaymentModal = true;
    }

    public function registerPayment(InvoiceService $service)
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:1|max:' . ($this->invoice->remaining_balance + 0.01),
            'paymentMethod' => 'required|string',
            'paymentReference' => 'nullable|string',
        ]);

        $service->registerPayment(
            $this->invoice,
            $this->paymentAmount,
            $this->paymentMethod,
            $this->paymentReference,
            $this->paymentNotes
        );

        $this->showPaymentModal = false;
        $this->invoice->refresh();
        $this->dispatch('notify', type: 'success', message: 'Paiement enregistré avec succès.');
    }

    public function markAsSent()
    {
        if ($this->invoice->status === 'draft') {
            $this->invoice->status = 'sent';
            $this->invoice->save();
            $this->dispatch('notify', type: 'success', message: 'Facture marquée comme envoyée.');
        }
    }

    public function openEmailModal()
    {
        $this->emailTo = $this->invoice->contact->email ?? '';
        $this->emailSubject = "Facture {$this->invoice->reference} - " . config('app.name');
        $this->emailMessage = "Bonjour,\n\nVeuillez trouver ci-joint la facture {$this->invoice->reference} d'un montant de " . number_format($this->invoice->total_amount_ttc, 0, ',', ' ') . " FCFA.\n\nDate d'échéance: {$this->invoice->due_date->format('d/m/Y')}\n\nCordialement,\n" . config('app.name');

        // Sélectionner la config SMTP par défaut
        $defaultSmtp = SmtpConfiguration::where('is_default', true)->where('is_active', true)->first();
        $this->selectedSmtpId = $defaultSmtp?->id;

        $this->showEmailModal = true;
    }

    public function sendInvoiceEmail()
    {
        $this->validate([
            'emailTo' => 'required|email',
            'emailSubject' => 'required|string|max:255',
            'emailMessage' => 'required|string',
            'selectedSmtpId' => 'required|exists:smtp_configurations,id',
        ]);

        try {
            $smtp = SmtpConfiguration::findOrFail($this->selectedSmtpId);

            // Appliquer la config SMTP
            $smtp->applyToMailer();

            // Générer le PDF
            $this->invoice->load(['contact', 'lines.product', 'creator', 'payments']);
            $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $this->invoice]);

            // Envoyer l'email
            Mail::send([], [], function ($message) use ($pdf) {
                $message->to($this->emailTo)
                    ->subject($this->emailSubject)
                    ->text($this->emailMessage)
                    ->attachData($pdf->output(), "facture-{$this->invoice->reference}.pdf", [
                        'mime' => 'application/pdf',
                    ]);
            });

            // Mettre à jour le statut si brouillon
            if ($this->invoice->status === 'draft') {
                $this->invoice->status = 'sent';
                $this->invoice->save();
            }

            $this->showEmailModal = false;
            $this->dispatch('notify', type: 'success', message: "Facture envoyée avec succès à {$this->emailTo}");

        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: "Erreur lors de l'envoi: " . $e->getMessage());
        }
    }

    public function getSmtpConfigurationsProperty()
    {
        return SmtpConfiguration::where('is_active', true)->orderBy('is_default', 'desc')->orderBy('name')->get();
    }

    public function confirmDelete()
    {
        $this->deletionReason = '';
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->deletionReason = '';
    }

    public function deleteInvoice()
    {
        $this->validate([
            'deletionReason' => 'required|string|min:5|max:500',
        ], [
            'deletionReason.required' => 'Veuillez indiquer la raison de la suppression.',
            'deletionReason.min' => 'La raison doit contenir au moins 5 caractères.',
        ]);

        // Vérifier les permissions (Super admin, Admin, Comptable, Manager)
        $user = auth()->user();
        if (!$user->hasAnyRole(['super-admin', 'admin', 'comptable', 'manager'])) {
            $this->dispatch('notify', type: 'error', message: "Vous n'avez pas les droits pour supprimer une facture.");
            $this->showDeleteModal = false;
            return;
        }

        // Vérifier si la facture peut être supprimée (pas de paiements)
        if ($this->invoice->payments->count() > 0) {
            $this->dispatch('notify', type: 'error', message: "Cette facture ne peut pas être supprimée car elle a des paiements enregistrés.");
            $this->showDeleteModal = false;
            return;
        }

        // Vérifier si la facture n'est pas payée
        if ($this->invoice->status === 'paid') {
            $this->dispatch('notify', type: 'error', message: "Une facture payée ne peut pas être supprimée.");
            $this->showDeleteModal = false;
            return;
        }

        $reference = $this->invoice->reference;

        // Enregistrer qui a supprimé et pourquoi
        $this->invoice->update([
            'deleted_by' => $user->id,
            'deletion_reason' => $this->deletionReason,
        ]);

        $this->invoice->lines()->delete();
        $this->invoice->delete();

        session()->flash('success', "Facture {$reference} supprimée avec succès.");
        $this->js('window.location.href = "' . route('finance.invoices.index') . '"');
    }

    public function render()
    {
        return view('finance::livewire.invoice-show', [
            'smtpConfigurations' => $this->smtpConfigurations,
        ])->layout('layouts.app');
    }
}
