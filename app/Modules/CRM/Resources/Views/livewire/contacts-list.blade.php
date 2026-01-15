<div class="space-y-6">
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">
                Contacts
            </h2>
            <p class="text-text-secondary text-sm">Gérez votre base de données clients et prospects.</p>
        </div>
        <div class="flex space-x-3">
            <x-ui.button wire:click="export" type="secondary" icon="download">
                Export
            </x-ui.button>
            <x-ui.button href="{{ route('crm.contacts.create') }}" type="primary" icon="add">
                Nouveau
            </x-ui.button>
        </div>
    </div>

    <!-- Filters -->
    <x-ui.card>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-ui.input wire:model.live.debounce.300ms="search" label="Recherche"
                placeholder="Nom, Entreprise, Email..." />

            <x-ui.select wire:model.live="type" label="Type">
                <option value="">Tous les types</option>
                <option value="prospect">Prospect</option>
                <option value="client">Client</option>
                <option value="fournisseur">Fournisseur</option>
            </x-ui.select>

            <x-ui.select wire:model.live="status" label="Statut">
                <option value="">Tous les statuts</option>
                <option value="active">Actif</option>
                <option value="inactive">Inactif</option>
            </x-ui.select>

            <x-ui.select wire:model.live="assigned_to" label="Assigné à">
                <option value="">Tous les utilisateurs</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </x-ui.select>
        </div>
    </x-ui.card>

    <!-- Table -->
    <x-ui.card class="overflow-hidden">
        <x-ui.table :headers="['Nom / Entreprise', 'Coordonnées', 'Type', 'Statut', 'Assigné à', 'Actions']">
            @forelse($contacts as $contact)
                <tr class="hover:bg-surface-highlight/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-white">{{ $contact->display_name }}</div>
                        @if($contact->company_name && $contact->company_name != $contact->full_name)
                            <div class="text-sm text-text-secondary">{{ $contact->full_name }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-text-secondary">
                        <div class="mb-1">
                            <a href="mailto:{{ $contact->email }}"
                                class="flex items-center gap-1 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-[14px]">mail</span>
                                {{ $contact->email }}
                            </a>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">call</span>
                            {{ $contact->phone }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <x-ui.badge :color="$contact->type === 'client' ? 'green' : ($contact->type === 'prospect' ? 'blue' : 'gray')">
                            {{ ucfirst($contact->type) }}
                        </x-ui.badge>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $contact->status === 'active' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' }}">
                            {{ $contact->status === 'active' ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-text-secondary">
                        @if($contact->user)
                            <div class="flex items-center gap-2">
                                <div class="size-6 rounded-full bg-cover bg-center border border-[#3a2e24]"
                                    style='background-image: url("https://ui-avatars.com/api/?name={{ urlencode($contact->user->name) }}&background=random&color=fff&size=64");'>
                                </div>
                                <span>{{ $contact->user->name }}</span>
                            </div>
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('crm.contacts.show', $contact) }}"
                                class="p-2 rounded-lg hover:bg-surface-highlight text-text-secondary hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                            </a>
                            <a href="{{ route('crm.contacts.edit', $contact) }}"
                                class="p-2 rounded-lg hover:bg-surface-highlight text-text-secondary hover:text-orange-400 transition-colors">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </a>
                            <button wire:click="confirmDeletion({{ $contact->id }})"
                                class="p-2 rounded-lg hover:bg-surface-highlight text-text-secondary hover:text-red-400 transition-colors">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-text-secondary">
                        <div class="flex flex-col items-center justify-center">
                            <span class="material-symbols-outlined text-4xl mb-2 opacity-50">person_off</span>
                            <p>Aucun contact trouvé.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-ui.table>
        <div class="border-t border-[#3a2e24] p-4">
            {{ $contacts->links() }}
        </div>
    </x-ui.card>

    <!-- Delete Modal -->
    <x-ui.modal name="confirm-contact-deletion" :show="$confirmingDeletion">
        <div class="p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl text-red-400">delete</span>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white">
                        Supprimer ce contact ?
                    </h2>
                    <p class="text-sm text-text-secondary">
                        Cette action est irréversible.
                    </p>
                </div>
            </div>
            <p class="text-text-secondary text-sm mb-6">
                Toutes les données associées à ce contact seront définitivement supprimées.
            </p>
            <div class="flex justify-end gap-3">
                <button wire:click="$set('confirmingDeletion', false)"
                    class="px-4 py-2 text-text-secondary hover:text-white hover:bg-surface-highlight rounded-xl transition-colors">
                    Annuler
                </button>
                <button wire:click="deleteContact"
                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-bold rounded-xl transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">delete</span>
                    Supprimer
                </button>
            </div>
        </div>
    </x-ui.modal>
</div>