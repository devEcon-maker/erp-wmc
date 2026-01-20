<div class="space-y-6">
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">
                Relances Clients
            </h2>
            <p class="text-text-secondary text-sm">Suivi des relances et communications avec les clients.</p>
        </div>
        <div class="flex space-x-3">
            <x-ui.button wire:click="create" type="primary" icon="add">
                Nouvelle Relance
            </x-ui.button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Total</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-text-secondary">notifications</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">En attente</p>
                    <p class="text-2xl font-bold text-blue-400">{{ $stats['pending'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-blue-500">schedule</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">En retard</p>
                    <p class="text-2xl font-bold text-red-400">{{ $stats['overdue'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-red-500">warning</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Aujourd'hui</p>
                    <p class="text-2xl font-bold text-amber-400">{{ $stats['due_today'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-amber-500">today</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Envoyees</p>
                    <p class="text-2xl font-bold text-indigo-400">{{ $stats['sent'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-indigo-500">send</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Sans reponse</p>
                    <p class="text-2xl font-bold text-orange-400">{{ $stats['no_response'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-orange-500">do_not_disturb</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Resolues</p>
                    <p class="text-2xl font-bold text-green-400">{{ $stats['resolved'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-green-500">check_circle</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Urgentes</p>
                    <p class="text-2xl font-bold text-rose-400">{{ $stats['urgent'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-rose-500">priority_high</span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <x-ui.card>
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="md:col-span-2">
                <x-ui.input wire:model.live.debounce.300ms="search" label="Recherche" placeholder="Sujet, Contact..." />
            </div>

            <x-ui.select wire:model.live="status" label="Statut">
                <option value="">Tous les statuts</option>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select wire:model.live="type" label="Type">
                <option value="">Tous les types</option>
                @foreach($types as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select wire:model.live="priority" label="Priorite">
                <option value="">Toutes</option>
                @foreach($priorities as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select wire:model.live="dateFilter" label="Echeance">
                <option value="">Toutes dates</option>
                <option value="overdue">En retard</option>
                <option value="today">Aujourd'hui</option>
                <option value="week">Cette semaine</option>
            </x-ui.select>
        </div>
    </x-ui.card>

    <!-- Table -->
    <x-ui.card class="overflow-visible">
        <x-ui.table :headers="['Priorite', 'Contact', 'Sujet', 'Type', 'Canal', 'Date prevue', 'Statut', 'N°', 'Actions']">
            @forelse($reminders as $reminder)
                <tr class="hover:bg-surface-highlight/50 transition-colors {{ $reminder->is_overdue ? 'bg-red-500/5' : ($reminder->is_due_today ? 'bg-amber-500/5' : '') }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $priorityColors = [
                                'low' => 'gray',
                                'normal' => 'blue',
                                'high' => 'orange',
                                'urgent' => 'red',
                            ];
                            $priorityIcons = [
                                'low' => 'arrow_downward',
                                'normal' => 'remove',
                                'high' => 'arrow_upward',
                                'urgent' => 'priority_high',
                            ];
                        @endphp
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs bg-{{ $priorityColors[$reminder->priority] ?? 'gray' }}-500/20 text-{{ $priorityColors[$reminder->priority] ?? 'gray' }}-400">
                            <span class="material-symbols-outlined text-[14px]">{{ $priorityIcons[$reminder->priority] ?? 'remove' }}</span>
                            {{ $reminder->priority_label }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-white">{{ $reminder->contact->display_name ?? '-' }}</div>
                        @if($reminder->contact && $reminder->contact->company_name)
                            <div class="text-xs text-text-secondary opacity-70">{{ $reminder->contact->company_name }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-text-secondary max-w-xs truncate" title="{{ $reminder->subject }}">
                            {{ $reminder->subject }}
                        </div>
                        @if($reminder->remindable)
                            <div class="text-xs text-primary/70">
                                @if($reminder->remindable_type === 'App\Modules\Finance\Models\Invoice')
                                    Facture: {{ $reminder->remindable->reference ?? '' }}
                                @elseif($reminder->remindable_type === 'App\Modules\CRM\Models\Proposal')
                                    Devis: {{ $reminder->remindable->reference ?? '' }}
                                @elseif($reminder->remindable_type === 'App\Modules\CRM\Models\Contract')
                                    Contrat: {{ $reminder->remindable->reference ?? '' }}
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">
                        {{ $reminder->type_label }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $channelIcons = [
                                'email' => 'mail',
                                'phone' => 'call',
                                'sms' => 'sms',
                                'meeting' => 'groups',
                                'letter' => 'mail',
                            ];
                        @endphp
                        <span class="inline-flex items-center gap-1 text-sm text-text-secondary">
                            <span class="material-symbols-outlined text-[16px]">{{ $channelIcons[$reminder->channel] ?? 'chat' }}</span>
                            {{ $reminder->channel_label }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm {{ $reminder->is_overdue ? 'text-red-400 font-medium' : ($reminder->is_due_today ? 'text-amber-400 font-medium' : 'text-text-secondary') }}">
                            {{ $reminder->scheduled_at?->format('d/m/Y H:i') ?? '-' }}
                        </div>
                        @if($reminder->is_overdue)
                            <div class="text-xs text-red-400">En retard</div>
                        @elseif($reminder->is_due_today)
                            <div class="text-xs text-amber-400">Aujourd'hui</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $statusColors = [
                                'pending' => 'blue',
                                'sent' => 'indigo',
                                'acknowledged' => 'green',
                                'no_response' => 'orange',
                                'resolved' => 'emerald',
                            ];
                        @endphp
                        <x-ui.badge :color="$statusColors[$reminder->status] ?? 'gray'">
                            {{ $reminder->status_label }}
                        </x-ui.badge>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full {{ $reminder->reminder_count > 2 ? 'bg-red-500/20 text-red-400' : 'bg-surface-highlight text-text-secondary' }} text-xs font-medium">
                            {{ $reminder->reminder_count }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-1" x-data="{ showActions: false }">
                            <button wire:click="edit({{ $reminder->id }})"
                                class="p-2 rounded-lg hover:bg-surface-highlight text-text-secondary hover:text-orange-400 transition-colors"
                                title="Modifier">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </button>

                            <!-- Menu deroulant Actions -->
                            <div class="relative">
                                <button @click="showActions = !showActions"
                                    class="p-2 rounded-lg hover:bg-surface-highlight text-text-secondary hover:text-white transition-colors"
                                    title="Plus d'actions">
                                    <span class="material-symbols-outlined text-[20px]">more_vert</span>
                                </button>
                                <div x-show="showActions"
                                    @click.away="showActions = false"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    style="display: none;"
                                    class="absolute right-0 bottom-full mb-2 w-56 rounded-xl bg-surface-dark border border-[#3a2e24] shadow-xl z-[100]">
                                    <div class="py-1">
                                        @if($reminder->status === 'pending')
                                            <button wire:click="markAsSent({{ $reminder->id }})" @click="showActions = false"
                                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-text-secondary hover:bg-surface-highlight hover:text-indigo-400 transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">send</span>
                                                Marquer comme envoyee
                                            </button>
                                            
                                            <button wire:click="openEmailModal({{ $reminder->id }})" @click="showActions = false" 
                                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-text-secondary hover:bg-surface-highlight hover:text-blue-400 transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">email</span>
                                                Envoyer par email
                                            </button>
                                        @endif

                                        @if(in_array($reminder->status, ['sent', 'no_response']))
                                            <button wire:click="openResponseModal({{ $reminder->id }})" @click="showActions = false"
                                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-text-secondary hover:bg-surface-highlight hover:text-green-400 transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">reply</span>
                                                Enregistrer reponse
                                            </button>
                                            <div class="border-t border-[#3a2e24] my-1"></div>
                                            <button wire:click="scheduleFollowUp({{ $reminder->id }}, 7)" @click="showActions = false"
                                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-text-secondary hover:bg-surface-highlight hover:text-amber-400 transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">schedule</span>
                                                Relancer dans 7 jours
                                            </button>
                                            <button wire:click="scheduleFollowUp({{ $reminder->id }}, 14)" @click="showActions = false"
                                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-text-secondary hover:bg-surface-highlight hover:text-amber-400 transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">schedule</span>
                                                Relancer dans 14 jours
                                            </button>
                                        @endif

                                        <div class="border-t border-[#3a2e24] my-1"></div>
                                        <button wire:click="delete({{ $reminder->id }})" @click="showActions = false"
                                            wire:confirm="Etes-vous sur de vouloir supprimer cette relance ?"
                                            class="w-full flex items-center gap-2 px-4 py-2 text-sm text-text-secondary hover:bg-surface-highlight hover:text-red-400 transition-colors">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                            Supprimer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center text-text-secondary">
                        <div class="flex flex-col items-center justify-center">
                            <span class="material-symbols-outlined text-4xl mb-2 opacity-50">notifications_off</span>
                            <p>Aucune relance trouvee.</p>
                            <button wire:click="create" class="mt-4 text-primary hover:underline">
                                Creer une nouvelle relance
                            </button>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-ui.table>
        <div class="border-t border-[#3a2e24] p-4">
            {{ $reminders->links() }}
        </div>
    </x-ui.card>

    <!-- Modal Creation/Edition -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-black/70 transition-opacity" wire:click="closeModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-surface-dark rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-[#3a2e24]">
                    <form wire:submit="save">
                        <div class="bg-surface-dark px-6 pt-6 pb-4">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xl font-bold text-white">
                                    {{ $editingReminder ? 'Modifier la relance' : 'Nouvelle relance' }}
                                </h3>
                                <button type="button" wire:click="closeModal" class="text-text-secondary hover:text-white">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>

                            <div class="space-y-4">
                                <!-- Contact -->
                                <x-ui.select wire:model.live="contact_id" label="Contact *" :error="$errors->first('contact_id')">
                                    <option value="">Selectionner un contact</option>
                                    @foreach($contacts as $contact)
                                        <option value="{{ $contact->id }}">
                                            {{ $contact->display_name }}
                                            @if($contact->company_name) - {{ $contact->company_name }} @endif
                                        </option>
                                    @endforeach
                                </x-ui.select>

                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Type de relance -->
                                    <x-ui.select wire:model.live="reminder_type" label="Type de relance *" :error="$errors->first('reminder_type')">
                                        @foreach($types as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </x-ui.select>

                                    <!-- Document lie -->
                                    @if($reminder_type !== 'general' && count($remindableOptions) > 0)
                                        <x-ui.select wire:model="remindable_id" label="Document lie">
                                            <option value="">Aucun</option>
                                            @foreach($remindableOptions as $option)
                                                <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                                            @endforeach
                                        </x-ui.select>
                                    @else
                                        <div></div>
                                    @endif
                                </div>

                                <!-- Sujet -->
                                <x-ui.input wire:model="subject" label="Sujet *" placeholder="Ex: Relance facture impayee" :error="$errors->first('subject')" />

                                <!-- Message -->
                                <x-ui.textarea wire:model="message" label="Message" rows="3" placeholder="Details de la relance..." />

                                <div class="grid grid-cols-3 gap-4">
                                    <!-- Canal -->
                                    <x-ui.select wire:model="form_channel" label="Canal *" :error="$errors->first('form_channel')">
                                        @foreach($channels as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </x-ui.select>

                                    <!-- Date prevue -->
                                    <x-ui.input type="datetime-local" wire:model="scheduled_at" label="Date prevue *" :error="$errors->first('scheduled_at')" />

                                    <!-- Priorite -->
                                    <x-ui.select wire:model="form_priority" label="Priorite *" :error="$errors->first('form_priority')">
                                        @foreach($priorities as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </div>
                            </div>
                        </div>

                        <div class="bg-surface-highlight px-6 py-4 flex justify-end gap-3">
                            <x-ui.button type="secondary" wire:click="closeModal">
                                Annuler
                            </x-ui.button>
                            <x-ui.button type="primary" submit>
                                <span wire:loading.remove wire:target="save">
                                    {{ $editingReminder ? 'Mettre a jour' : 'Creer la relance' }}
                                </span>
                                <span wire:loading wire:target="save" class="flex items-center gap-2">
                                    <span class="material-symbols-outlined animate-spin text-[18px]">refresh</span>
                                    Enregistrement...
                                </span>
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Reponse -->
    @if($showResponseModal && $respondingReminder)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-black/70 transition-opacity" wire:click="closeModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-surface-dark rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-[#3a2e24]">
                    <div class="bg-surface-dark px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-white">Enregistrer la reponse</h3>
                            <button type="button" wire:click="closeModal" class="text-text-secondary hover:text-white">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <!-- Info relance -->
                            <div class="bg-surface-highlight rounded-lg p-4">
                                <p class="text-sm font-medium text-white">{{ $respondingReminder->subject }}</p>
                                <p class="text-xs text-text-secondary mt-1">
                                    {{ $respondingReminder->contact->display_name ?? '' }} -
                                    Envoyee le {{ $respondingReminder->sent_at?->format('d/m/Y') ?? '-' }}
                                </p>
                            </div>

                            <!-- Statut de reponse -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-text-secondary">Resultat</label>
                                <div class="space-y-2">
                                    <label class="flex items-center p-3 border border-[#3a2e24] rounded-lg cursor-pointer hover:bg-surface-highlight transition-colors {{ $response_status === 'acknowledged' ? 'border-green-500 bg-green-500/10' : '' }}">
                                        <input type="radio" wire:model="response_status" value="acknowledged" class="mr-3 text-green-500 focus:ring-green-500">
                                        <span class="material-symbols-outlined text-green-400 mr-2">check_circle</span>
                                        <span class="text-text-secondary">Client a accuse reception</span>
                                    </label>
                                    <label class="flex items-center p-3 border border-[#3a2e24] rounded-lg cursor-pointer hover:bg-surface-highlight transition-colors {{ $response_status === 'no_response' ? 'border-orange-500 bg-orange-500/10' : '' }}">
                                        <input type="radio" wire:model="response_status" value="no_response" class="mr-3 text-orange-500 focus:ring-orange-500">
                                        <span class="material-symbols-outlined text-orange-400 mr-2">schedule</span>
                                        <span class="text-text-secondary">Toujours sans reponse</span>
                                    </label>
                                    <label class="flex items-center p-3 border border-[#3a2e24] rounded-lg cursor-pointer hover:bg-surface-highlight transition-colors {{ $response_status === 'resolved' ? 'border-emerald-500 bg-emerald-500/10' : '' }}">
                                        <input type="radio" wire:model="response_status" value="resolved" class="mr-3 text-emerald-500 focus:ring-emerald-500">
                                        <span class="material-symbols-outlined text-emerald-400 mr-2">task_alt</span>
                                        <span class="text-text-secondary">Probleme resolu</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Notes -->
                            <x-ui.textarea wire:model="response_notes" label="Notes" rows="3" placeholder="Notes sur la reponse du client..." />
                        </div>
                    </div>

                    <div class="bg-surface-highlight px-6 py-4 flex justify-end gap-3">
                        <x-ui.button type="secondary" wire:click="closeModal">
                            Annuler
                        </x-ui.button>
                        <x-ui.button type="primary" wire:click="saveResponse">
                            Enregistrer
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Email -->
    @if($showEmailModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-black/70 transition-opacity" wire:click="closeModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-surface-dark rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-[#3a2e24]">
                    <div class="bg-surface-dark px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-white">Envoyer par email</h3>
                            <button type="button" wire:click="closeModal" class="text-text-secondary hover:text-white">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <!-- SMTP Selection -->
                            <x-ui.select wire:model="selectedSmtpId" label="Serveur d'envoi">
                                @foreach($smtpConfigurations as $smtp)
                                    <option value="{{ $smtp->id }}">{{ $smtp->name }} ({{ $smtp->username }})</option>
                                @endforeach
                            </x-ui.select>

                            <!-- Destinataire -->
                            <x-ui.input wire:model="emailTo" label="Destinataire (Email)" type="email" />

                            <!-- Sujet -->
                            <x-ui.input wire:model="emailSubject" label="Sujet" />

                            <!-- Message -->
                            <x-ui.textarea wire:model="emailMessage" label="Message" rows="6" />
                        </div>
                    </div>

                    <div class="bg-surface-highlight px-6 py-4 flex justify-end gap-3">
                        <x-ui.button type="secondary" wire:click="closeModal">
                            Annuler
                        </x-ui.button>
                        <x-ui.button type="primary" wire:click="sendReminderEmail">
                            Envoyer
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
