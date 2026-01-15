<div class="space-y-6">
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">
                Contrats Physiques
            </h2>
            <p class="text-text-secondary text-sm">Gestion des contrats clients, fournisseurs et prestataires avec archivage documentaire.</p>
        </div>
        <div class="flex space-x-3">
            <x-ui.button wire:click="create" type="primary" icon="add">
                Nouveau Contrat
            </x-ui.button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Total</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-text-secondary">folder</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Actifs</p>
                    <p class="text-2xl font-bold text-green-400">{{ $stats['active'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-green-500">check_circle</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Clients</p>
                    <p class="text-2xl font-bold text-blue-400">{{ $stats['clients'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-blue-500">person</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Fournisseurs</p>
                    <p class="text-2xl font-bold text-purple-400">{{ $stats['fournisseurs'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-purple-500">local_shipping</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Prestataires</p>
                    <p class="text-2xl font-bold text-orange-400">{{ $stats['prestataires'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-orange-500">engineering</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Avec Doc.</p>
                    <p class="text-2xl font-bold text-cyan-400">{{ $stats['with_document'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-cyan-500">attach_file</span>
            </div>
        </div>
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-text-secondary text-xs uppercase">Expire bientot</p>
                    <p class="text-2xl font-bold text-red-400">{{ $stats['expiring_soon'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-red-500">schedule</span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <x-ui.card>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <x-ui.input wire:model.live.debounce.300ms="search" label="Recherche" placeholder="Reference, Contact, Signataire..." />

            <x-ui.select wire:model.live="status" label="Statut">
                <option value="">Tous les statuts</option>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select wire:model.live="category" label="Categorie">
                <option value="">Toutes categories</option>
                @foreach($categories as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select wire:model.live="hasDocument" label="Document">
                <option value="">Tous</option>
                <option value="yes">Avec document</option>
                <option value="no">Sans document</option>
            </x-ui.select>
        </div>
    </x-ui.card>

    <!-- Table -->
    <x-ui.card class="overflow-hidden">
        <x-ui.table :headers="['Reference', 'Contact', 'Categorie', 'Type', 'Periode', 'Document', 'Statut', 'Actions']">
            @forelse($contracts as $contract)
                <tr class="hover:bg-surface-highlight/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-white">{{ $contract->reference }}</div>
                        @if($contract->signatory_name)
                            <div class="text-xs text-text-secondary">Signataire: {{ $contract->signatory_name }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-text-secondary">{{ $contract->contact->display_name ?? '-' }}</div>
                        @if($contract->contact && $contract->contact->company_name)
                            <div class="text-xs text-text-secondary opacity-70">{{ $contract->contact->company_name }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $categoryColors = [
                                'client' => 'blue',
                                'fournisseur' => 'purple',
                                'prestataire' => 'orange',
                            ];
                            $categoryIcons = [
                                'client' => 'person',
                                'fournisseur' => 'local_shipping',
                                'prestataire' => 'engineering',
                            ];
                        @endphp
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs bg-{{ $categoryColors[$contract->contract_category ?? 'client'] ?? 'gray' }}-500/20 text-{{ $categoryColors[$contract->contract_category ?? 'client'] ?? 'gray' }}-400">
                            <span class="material-symbols-outlined text-[14px]">{{ $categoryIcons[$contract->contract_category ?? 'client'] ?? 'folder' }}</span>
                            {{ $contract->category_label }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-text-secondary">
                        {{ $contract->subtype_label }}
                    </td>
                    <td class="px-6 py-4 text-sm text-text-secondary">
                        <div class="flex flex-col">
                            <span>Du {{ $contract->start_date?->format('d/m/Y') ?? '-' }}</span>
                            <span class="text-xs opacity-70">au {{ $contract->end_date?->format('d/m/Y') ?? 'Indefini' }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($contract->has_document)
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs bg-green-500/20 text-green-400">
                                    <span class="material-symbols-outlined text-[14px]">
                                        {{ $contract->document_type === 'pdf' ? 'picture_as_pdf' : 'description' }}
                                    </span>
                                    {{ strtoupper($contract->document_type) }}
                                </span>
                                <button wire:click="downloadDocument({{ $contract->id }})"
                                    class="p-1 rounded hover:bg-surface-highlight text-text-secondary hover:text-cyan-400 transition-colors"
                                    title="Telecharger">
                                    <span class="material-symbols-outlined text-[16px]">download</span>
                                </button>
                            </div>
                        @else
                            <span class="text-xs text-text-secondary/50">Aucun document</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $statusColors = [
                                'draft' => 'gray',
                                'active' => 'green',
                                'suspended' => 'orange',
                                'expired' => 'red',
                                'terminated' => 'red',
                            ];
                        @endphp
                        <x-ui.badge :color="$statusColors[$contract->status] ?? 'gray'">
                            {{ $contract->status_label }}
                        </x-ui.badge>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-1" x-data="{ showActions: false }">
                            <button wire:click="edit({{ $contract->id }})"
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
                                    class="absolute right-0 mt-2 w-56 rounded-xl bg-surface-dark border border-[#3a2e24] shadow-lg z-50">
                                    <div class="py-1">
                                        @if($contract->has_document)
                                            <button wire:click="downloadDocument({{ $contract->id }})" @click="showActions = false"
                                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-text-secondary hover:bg-surface-highlight hover:text-cyan-400 transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">download</span>
                                                Telecharger le document
                                            </button>
                                            <button wire:click="removeDocument({{ $contract->id }})" @click="showActions = false"
                                                wire:confirm="Etes-vous sur de vouloir supprimer ce document ?"
                                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-text-secondary hover:bg-surface-highlight hover:text-red-400 transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                                Supprimer le document
                                            </button>
                                            <div class="border-t border-[#3a2e24] my-1"></div>
                                        @endif

                                        @if(in_array($contract->status, ['active', 'suspended', 'draft']))
                                            <button wire:click="toggleStatus({{ $contract->id }})" @click="showActions = false"
                                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-text-secondary hover:bg-surface-highlight hover:text-yellow-400 transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">
                                                    {{ $contract->status === 'active' ? 'pause' : 'play_arrow' }}
                                                </span>
                                                {{ $contract->status === 'active' ? 'Suspendre' : 'Activer' }}
                                            </button>
                                        @endif

                                        @if(!in_array($contract->status, ['terminated', 'expired']))
                                            <button wire:click="terminateContract({{ $contract->id }})" @click="showActions = false"
                                                wire:confirm="Etes-vous sur de vouloir resilier ce contrat ?"
                                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-text-secondary hover:bg-surface-highlight hover:text-red-400 transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">cancel</span>
                                                Resilier le contrat
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-text-secondary">
                        <div class="flex flex-col items-center justify-center">
                            <span class="material-symbols-outlined text-4xl mb-2 opacity-50">folder_off</span>
                            <p>Aucun contrat trouve.</p>
                            <button wire:click="create" class="mt-4 text-primary hover:underline">
                                Creer un nouveau contrat
                            </button>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-ui.table>
        <div class="border-t border-[#3a2e24] p-4">
            {{ $contracts->links() }}
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
                                    {{ $editingContract ? 'Modifier le contrat' : 'Nouveau contrat physique' }}
                                </h3>
                                <button type="button" wire:click="closeModal" class="text-text-secondary hover:text-white">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>

                            <div class="space-y-4">
                                <!-- Contact -->
                                <x-ui.select wire:model="contact_id" label="Contact *" :error="$errors->first('contact_id')">
                                    <option value="">Selectionner un contact</option>
                                    @foreach($contacts as $contact)
                                        <option value="{{ $contact->id }}">
                                            {{ $contact->display_name }}
                                            @if($contact->company_name) - {{ $contact->company_name }} @endif
                                            ({{ ucfirst($contact->type) }})
                                        </option>
                                    @endforeach
                                </x-ui.select>

                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Categorie -->
                                    <x-ui.select wire:model="contract_category" label="Categorie *" :error="$errors->first('contract_category')">
                                        @foreach($categories as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </x-ui.select>

                                    <!-- Sous-type -->
                                    <x-ui.select wire:model="contract_subtype" label="Type de contrat">
                                        <option value="">Selectionner un type</option>
                                        @foreach($subtypes as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Date debut -->
                                    <x-ui.input type="date" wire:model="start_date" label="Date de debut *" :error="$errors->first('start_date')" />

                                    <!-- Date fin -->
                                    <x-ui.input type="date" wire:model="end_date" label="Date de fin" :error="$errors->first('end_date')" />
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Signataire -->
                                    <x-ui.input wire:model="signatory_name" label="Nom du signataire" placeholder="Jean Dupont" :error="$errors->first('signatory_name')" />

                                    <!-- Date signature -->
                                    <x-ui.input type="date" wire:model="signature_date" label="Date de signature" :error="$errors->first('signature_date')" />
                                </div>

                                <!-- Notes -->
                                <x-ui.textarea wire:model="notes" label="Notes" rows="2" placeholder="Informations complementaires..." />

                                <!-- Upload Document -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-text-secondary">
                                        Document du contrat (PDF, DOCX - Max 10 Mo)
                                    </label>

                                    @if($editingContract && $editingContract->has_document)
                                        <div class="flex items-center gap-3 p-3 bg-surface-highlight rounded-lg">
                                            <span class="material-symbols-outlined text-green-400">
                                                {{ $editingContract->document_type === 'pdf' ? 'picture_as_pdf' : 'description' }}
                                            </span>
                                            <div class="flex-1">
                                                <p class="text-sm text-white">{{ $editingContract->document_name }}</p>
                                                <p class="text-xs text-text-secondary">{{ $editingContract->formatted_document_size }}</p>
                                            </div>
                                            <span class="text-xs text-text-secondary">Document actuel</span>
                                        </div>
                                    @endif

                                    <div class="relative">
                                        <input type="file" wire:model="document" accept=".pdf,.doc,.docx"
                                            class="block w-full text-sm text-text-secondary
                                                file:mr-4 file:py-2 file:px-4
                                                file:rounded-lg file:border-0
                                                file:text-sm file:font-semibold
                                                file:bg-primary/20 file:text-primary
                                                hover:file:bg-primary/30
                                                cursor-pointer" />
                                        <div wire:loading wire:target="document" class="absolute inset-0 flex items-center justify-center bg-surface-dark/80 rounded-lg">
                                            <span class="material-symbols-outlined animate-spin text-primary">refresh</span>
                                            <span class="ml-2 text-sm text-primary">Chargement...</span>
                                        </div>
                                    </div>
                                    @error('document')
                                        <p class="text-sm text-red-400">{{ $message }}</p>
                                    @enderror

                                    @if($document)
                                        <div class="flex items-center gap-2 p-2 bg-green-500/10 rounded-lg">
                                            <span class="material-symbols-outlined text-green-400 text-[18px]">check_circle</span>
                                            <span class="text-sm text-green-400">Nouveau fichier selectionne: {{ $document->getClientOriginalName() }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="bg-surface-highlight px-6 py-4 flex justify-end gap-3">
                            <x-ui.button type="secondary" wire:click="closeModal">
                                Annuler
                            </x-ui.button>
                            <x-ui.button type="primary" submit>
                                <span wire:loading.remove wire:target="save">
                                    {{ $editingContract ? 'Mettre a jour' : 'Creer le contrat' }}
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
</div>
