<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white">
                {{ $contact->exists ? 'Modifier ' . $contact->display_name : 'Nouveau Contact' }}
            </h2>
            <p class="text-text-secondary text-sm">Informations générales et coordonnées.</p>
        </div>
    </div>

    <form wire:submit="save">
        <x-ui.card>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Type & Status -->
                <x-ui.select wire:model="type" label="Type" :error="$errors->first('type')">
                    <option value="prospect">Prospect</option>
                    <option value="client">Client</option>
                    <option value="fournisseur">Fournisseur</option>
                </x-ui.select>

                <x-ui.select wire:model="status" label="Statut" :error="$errors->first('status')">
                    <option value="active">Actif</option>
                    <option value="inactive">Inactif</option>
                </x-ui.select>

                <!-- Identity -->
                <x-ui.input wire:model="company_name" label="Nom de l'entreprise"
                    :error="$errors->first('company_name')" />

                <div class="grid grid-cols-2 gap-4">
                    <x-ui.input wire:model="first_name" label="Prénom" :error="$errors->first('first_name')" />
                    <x-ui.input wire:model="last_name" label="Nom" :error="$errors->first('last_name')" />
                </div>

                <!-- Contact -->
                <x-ui.input wire:model="email" type="email" label="Email" :error="$errors->first('email')" />

                <div class="grid grid-cols-2 gap-4">
                    <x-ui.input wire:model="phone" label="Téléphone" :error="$errors->first('phone')" />
                    <x-ui.input wire:model="mobile" label="Mobile" :error="$errors->first('mobile')" />
                </div>

                <!-- Address -->
                <x-ui.textarea wire:model="address" label="Adresse" :error="$errors->first('address')"
                    class="h-24 md:col-span-2" />

                <div class="md:col-span-2 grid grid-cols-3 gap-4">
                    <x-ui.input wire:model="postal_code" label="Code Postal" :error="$errors->first('postal_code')" />
                    <x-ui.input wire:model="city" label="Ville" :error="$errors->first('city')" />
                    <x-ui.input wire:model="country" label="Pays" :error="$errors->first('country')" />
                </div>

                <!-- Assignment -->
                <x-ui.select wire:model="assigned_to" label="Assigné à" :error="$errors->first('assigned_to')">
                    <option value="">Sélectionner...</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.input wire:model="source" label="Source" :error="$errors->first('source')"
                    placeholder="Ex: Linkedin, Email..." />

                <!-- Notes -->
                <x-ui.textarea wire:model="notes" label="Notes" :error="$errors->first('notes')"
                    class="h-32 md:col-span-2" />
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <x-ui.button type="secondary" href="{{ route('crm.contacts.index') }}">
                    Annuler
                </x-ui.button>
                <x-ui.button type="primary" :submit="true" wire:loading.attr="disabled">
                    <span wire:loading.remove>Enregistrer</span>
                    <span wire:loading class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Enregistrement...
                    </span>
                </x-ui.button>
            </div>
        </x-ui.card>
    </form>
</div>