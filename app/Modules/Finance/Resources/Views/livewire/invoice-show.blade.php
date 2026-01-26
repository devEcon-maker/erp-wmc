<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <h2 class="text-2xl font-bold text-white tracking-tight">
                    {{ $invoice->reference }}
                </h2>
                @php
                    $statusColors = [
                        'draft' => 'gray',
                        'sent' => 'blue',
                        'partial' => 'orange',
                        'paid' => 'green',
                        'overdue' => 'red',
                        'cancelled' => 'black',
                    ];
                     $statusLabels = [
                        'draft' => 'Brouillon',
                        'sent' => 'Envoyée',
                        'partial' => 'Partiel',
                        'paid' => 'Payée',
                        'overdue' => 'En Retard',
                        'cancelled' => 'Annulée',
                    ];
                @endphp
                <x-ui.badge :color="$statusColors[$invoice->status] ?? 'gray'">
                    {{ $statusLabels[$invoice->status] ?? ucfirst($invoice->status) }}
                </x-ui.badge>
            </div>
            <p class="text-text-secondary text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-[16px]">business</span>
                {{ $invoice->contact->display_name }}
            </p>
        </div>
        
        <div class="flex flex-wrap gap-3">
            <x-ui.button type="secondary" icon="edit" :href="route('finance.invoices.edit', $invoice)">
                Modifier
            </x-ui.button>
            <x-ui.button type="secondary" icon="print" :href="route('finance.invoices.pdf', $invoice)">
                PDF
            </x-ui.button>
            <x-ui.button wire:click="openEmailModal" type="secondary" icon="mail">
                Envoyer
            </x-ui.button>

            @if($invoice->status === 'draft')
                <x-ui.button wire:click="markAsSent" type="primary" icon="send">
                    Marquer comme envoyée
                </x-ui.button>
            @endif

            @if($invoice->remaining_balance > 0 && $invoice->status !== 'draft')
                <x-ui.button wire:click="openPaymentModal" type="primary" icon="payments">
                    Enregistrer un paiement
                </x-ui.button>
            @endif

            @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'comptable', 'manager']))
                <button wire:click="confirmDelete"
                    class="px-4 py-2 rounded-xl text-red-400 hover:text-white hover:bg-red-500/20 border border-red-500/20 font-bold transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                    Supprimer
                </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Document -->
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card class="overflow-hidden">
                <!-- Lines Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-sm text-text-secondary border-b border-[#3a2e24]">
                                <th class="p-4 font-bold uppercase">Description</th>
                                <th class="p-4 font-bold uppercase text-right">Prix Unitaire</th>
                                <th class="p-4 font-bold uppercase text-center">Qté</th>
                                <th class="p-4 font-bold uppercase text-right">Total HT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#3a2e24]">
                            @foreach($invoice->lines as $line)
                                <tr class="text-white hover:bg-surface-highlight/20">
                                    <td class="p-4">
                                        <div class="font-bold">{{ $line->description }}</div>
                                    </td>
                                    <td class="p-4 text-right">{{ number_format($line->unit_price, 2, ',', ' ') }}</td>
                                    <td class="p-4 text-center">{{ $line->quantity }}</td>
                                    <td class="p-4 text-right font-bold">{{ number_format($line->total_amount, 2, ',', ' ') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-surface-dark border-t border-[#3a2e24]">
                            <tr>
                                <td colspan="3" class="p-4 text-right text-text-secondary">Total HT</td>
                                <td class="p-4 text-right font-bold text-white">{{ number_format($invoice->total_amount, 2, ',', ' ') }}</td>
                            </tr>
                             <tr>
                                <td colspan="3" class="p-4 text-right text-text-secondary">Remise</td>
                                <td class="p-4 text-right font-bold text-white">{{ number_format($invoice->discount_amount, 2, ',', ' ') }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="p-4 text-right text-text-secondary">TVA</td>
                                <td class="p-4 text-right font-bold text-white">{{ number_format($invoice->tax_amount, 2, ',', ' ') }}</td>
                            </tr>
                            <tr class="text-lg bg-surface-highlight/30">
                                <td colspan="3" class="p-4 text-right font-bold text-white">Total TTC</td>
                                <td class="p-4 text-right font-bold text-primary">{{ number_format($invoice->total_amount_ttc, 2, ',', ' ') }} FCFA</td>
                            </tr>
                            @if($invoice->paid_amount > 0)
                                <tr>
                                    <td colspan="3" class="p-4 text-right text-green-500 font-bold">Déjà Payé</td>
                                    <td class="p-4 text-right font-bold text-green-500">- {{ number_format($invoice->paid_amount, 2, ',', ' ') }}</td>
                                </tr>
                                 <tr class="text-lg border-t border-[#3a2e24]">
                                    <td colspan="3" class="p-4 text-right font-bold text-white">Reste à Payer</td>
                                    <td class="p-4 text-right font-bold text-orange-400">{{ number_format($invoice->remaining_balance, 2, ',', ' ') }} FCFA</td>
                                </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>
            </x-ui.card>
            
              @if($invoice->payments->count() > 0)
                <x-ui.card title="Historique des paiements">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-text-secondary border-b border-[#3a2e24]">
                                <th class="pb-2">Date</th>
                                <th class="pb-2">Mode</th>
                                <th class="pb-2">Référence</th>
                                <th class="pb-2 text-right">Montant</th>
                            </tr>
                        </thead>
                         <tbody class="divide-y divide-[#3a2e24]">
                            @foreach($invoice->payments as $payment)
                                <tr class="text-white group">
                                    <td class="py-3">{{ $payment->payment_date->format('d/m/Y') }}</td>
                                    <td class="py-3">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                    <td class="py-3 text-text-secondary">{{ $payment->reference ?? '-' }}</td>
                                    <td class="py-3 text-right font-bold">{{ number_format($payment->amount, 2, ',', ' ') }} FCFA</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-ui.card>
            @endif
        </div>

         <!-- Sidebar Info -->
        <div class="space-y-6">
            <x-ui.card title="Détails">
                <div class="space-y-4">
                     <div class="flex justify-between items-center text-sm">
                        <span class="text-text-secondary">Date d'émission</span>
                        <span class="text-white font-bold">{{ $invoice->order_date->format('d/m/Y') }}</span>
                    </div>
                     <div class="flex justify-between items-center text-sm">
                        <span class="text-text-secondary">Date d'échéance</span>
                        <span class="text-white font-bold {{ $invoice->status === 'overdue' ? 'text-red-500' : '' }}">{{ $invoice->due_date->format('d/m/Y') }}</span>
                    </div>
                    
                    @if($invoice->created_by)
                         <div class="border-t border-[#3a2e24] my-2"></div>
                         <div class="text-xs text-text-secondary">Créé par {{ $invoice->creator->name }}</div>
                    @endif
                </div>
            </x-ui.card>
            
            @if($invoice->notes)
                 <x-ui.card title="Notes">
                    <p class="text-sm text-text-secondary italic">{{ $invoice->notes }}</p>
                </x-ui.card>
            @endif
        </div>
    </div>
    
     <!-- Payment Modal -->
    <div x-data="{ show: @entangle('showPaymentModal') }"
         x-show="show"
         x-transition
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="show = false"></div>
        
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-2xl bg-surface-dark border border-[#3a2e24] p-6 shadow-xl">
                <h3 class="text-xl font-bold text-white mb-4">Enregistrer un paiement</h3>
                
                <div class="space-y-4">
                     <div>
                        <label class="text-sm font-medium text-text-secondary mb-1 block">Montant</label>
                        <input type="number" step="0.01" wire:model="paymentAmount"
                               class="w-full bg-background-dark border border-[#3a2e24] text-white rounded-lg p-3 focus:ring-1 focus:ring-primary outline-none" />
                        @error('paymentAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                     <div>
                        <label class="text-sm font-medium text-text-secondary mb-1 block">Mode de paiement</label>
                        <select wire:model="paymentMethod"
                                class="w-full bg-background-dark border border-[#3a2e24] text-white rounded-lg p-3 focus:ring-1 focus:ring-primary outline-none">
                            <option value="bank_transfer">Virement Bancaire</option>
                            <option value="check">Chèque</option>
                            <option value="cash">Espèces</option>
                            <option value="card">Carte Bancaire</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-text-secondary mb-1 block">Référence (Optionnel)</label>
                        <input type="text" wire:model="paymentReference" placeholder="N° Virement, N° Chèque..."
                               class="w-full bg-background-dark border border-[#3a2e24] text-white rounded-lg p-3 focus:ring-1 focus:ring-primary outline-none" />
                    </div>
                    
                     <div>
                        <label class="text-sm font-medium text-text-secondary mb-1 block">Notes (Optionnel)</label>
                        <textarea wire:model="paymentNotes" rows="2"
                               class="w-full bg-background-dark border border-[#3a2e24] text-white rounded-lg p-3 focus:ring-1 focus:ring-primary outline-none"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button @click="show = false" class="px-4 py-2 text-text-secondary hover:text-white transition-colors">Annuler</button>
                    <button wire:click="registerPayment" class="bg-primary hover:bg-primary-light text-white font-bold py-2 px-4 rounded-xl transition-all shadow-lg shadow-primary/20">
                        Valider le paiement
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Modal -->
    <div x-data="{ show: @entangle('showEmailModal') }"
         x-show="show"
         x-transition
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="show = false"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-lg rounded-2xl bg-surface-dark border border-[#3a2e24] p-6 shadow-xl">
                <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">mail</span>
                    Envoyer la facture par email
                </h3>

                <div class="space-y-4">
                    <!-- Configuration SMTP -->
                    <div>
                        <label class="text-sm font-medium text-text-secondary mb-1 block">Configuration SMTP *</label>
                        <select wire:model="selectedSmtpId"
                                class="w-full bg-background-dark border border-[#3a2e24] text-white rounded-lg p-3 focus:ring-1 focus:ring-primary outline-none">
                            <option value="">-- Sélectionner une configuration --</option>
                            @foreach($smtpConfigurations as $smtp)
                                <option value="{{ $smtp->id }}">
                                    {{ $smtp->name }}
                                    @if($smtp->is_default) (Par défaut) @endif
                                    - {{ $smtp->from_address }}
                                </option>
                            @endforeach
                        </select>
                        @error('selectedSmtpId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        @if($smtpConfigurations->isEmpty())
                            <p class="text-yellow-500 text-xs mt-1">
                                <span class="material-symbols-outlined text-[14px] align-middle">warning</span>
                                Aucune configuration SMTP active. <a href="{{ route('admin.settings') }}" class="underline">Configurer</a>
                            </p>
                        @endif
                    </div>

                    <!-- Destinataire -->
                    <div>
                        <label class="text-sm font-medium text-text-secondary mb-1 block">Destinataire *</label>
                        <input type="email" wire:model="emailTo" placeholder="email@exemple.com"
                               class="w-full bg-background-dark border border-[#3a2e24] text-white rounded-lg p-3 focus:ring-1 focus:ring-primary outline-none" />
                        @error('emailTo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Sujet -->
                    <div>
                        <label class="text-sm font-medium text-text-secondary mb-1 block">Sujet *</label>
                        <input type="text" wire:model="emailSubject"
                               class="w-full bg-background-dark border border-[#3a2e24] text-white rounded-lg p-3 focus:ring-1 focus:ring-primary outline-none" />
                        @error('emailSubject') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Message -->
                    <div>
                        <label class="text-sm font-medium text-text-secondary mb-1 block">Message</label>
                        <textarea wire:model="emailMessage" rows="6"
                                  class="w-full bg-background-dark border border-[#3a2e24] text-white rounded-lg p-3 focus:ring-1 focus:ring-primary outline-none"></textarea>
                        @error('emailMessage') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Info pièce jointe -->
                    <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-3">
                        <p class="text-blue-400 text-sm flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">attach_file</span>
                            La facture PDF sera automatiquement jointe à l'email.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button @click="show = false" class="px-4 py-2 text-text-secondary hover:text-white transition-colors">Annuler</button>
                    <button wire:click="sendInvoiceEmail"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="bg-primary hover:bg-primary-light text-white font-bold py-2 px-4 rounded-xl transition-all shadow-lg shadow-primary/20 flex items-center gap-2">
                        <span wire:loading wire:target="sendInvoiceEmail" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                        <span wire:loading.remove wire:target="sendInvoiceEmail" class="material-symbols-outlined text-[18px]">send</span>
                        Envoyer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6 max-w-md w-full shadow-xl">
                <div class="flex items-center gap-3 mb-4">
                    <div class="size-12 rounded-full bg-red-500/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-red-400 text-2xl">warning</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Confirmer la suppression</h3>
                        <p class="text-text-secondary text-sm">Cette action est irréversible</p>
                    </div>
                </div>
                <p class="text-text-secondary mb-4">
                    Êtes-vous sûr de vouloir supprimer la facture <strong class="text-white">{{ $invoice->reference }}</strong> ?
                    <br>
                    <span class="text-xs">Montant: {{ number_format($invoice->total_amount_ttc, 0, ',', ' ') }} FCFA</span>
                </p>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-text-secondary mb-2">
                        Raison de la suppression <span class="text-red-400">*</span>
                    </label>
                    <textarea wire:model="deletionReason"
                        rows="3"
                        placeholder="Indiquez la raison de la suppression..."
                        class="w-full px-3 py-2 bg-surface-highlight border border-[#3a2e24] rounded-xl text-white placeholder-text-secondary focus:border-primary-500 focus:ring-1 focus:ring-primary-500"></textarea>
                    @error('deletionReason')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-xl mb-4">
                    <p class="text-xs text-amber-400">
                        <span class="material-symbols-outlined text-[14px] align-middle">info</span>
                        Cette suppression sera enregistrée avec votre nom, la date et l'heure.
                    </p>
                </div>

                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete"
                        class="px-4 py-2 rounded-xl border border-[#3a2e24] text-text-secondary hover:text-white hover:bg-surface-highlight font-medium transition-colors">
                        Annuler
                    </button>
                    <button wire:click="deleteInvoice"
                        class="px-4 py-2 rounded-xl bg-red-500 text-white hover:bg-red-600 font-medium transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
