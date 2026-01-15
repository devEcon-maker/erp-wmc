<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white">
                {{ $project?->exists ? 'Modifier le projet' : 'Nouveau Projet' }}
            </h2>
            <p class="text-text-secondary text-sm">
                {{ $project?->exists ? 'Modifiez les informations du projet.' : 'Créez un nouveau projet.' }}
            </p>
        </div>
        <x-ui.button href="{{ route('productivity.projects.index') }}" type="secondary" class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            Retour
        </x-ui.button>
    </div>

    <form wire:submit="save" class="space-y-6">
        <!-- Informations générales -->
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">info</span>
                Informations générales
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <x-ui.input
                        label="Nom du projet *"
                        wire:model="name"
                        placeholder="Nom du projet"
                        :error="$errors->first('name')"
                    />
                </div>

                <div class="md:col-span-2">
                    <x-ui.textarea
                        label="Description"
                        wire:model="description"
                        placeholder="Description du projet..."
                        rows="3"
                        :error="$errors->first('description')"
                    />
                </div>

                <div>
                    <x-ui.select
                        label="Client"
                        wire:model="contact_id"
                        :error="$errors->first('contact_id')"
                    >
                        <option value="">Sélectionner un client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div>
                    <x-ui.select
                        label="Manager *"
                        wire:model="manager_id"
                        :error="$errors->first('manager_id')"
                    >
                        <option value="">Sélectionner un manager</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div>
                    <x-ui.input
                        type="date"
                        label="Date de début"
                        wire:model="start_date"
                        :error="$errors->first('start_date')"
                    />
                </div>

                <div>
                    <x-ui.input
                        type="date"
                        label="Date de fin"
                        wire:model="end_date"
                        :error="$errors->first('end_date')"
                    />
                </div>

                <div>
                    <x-ui.select
                        label="Statut *"
                        wire:model="status"
                        :error="$errors->first('status')"
                    >
                        <option value="planning">Planification</option>
                        <option value="active">Actif</option>
                        <option value="on_hold">En pause</option>
                        <option value="completed">Terminé</option>
                        <option value="cancelled">Annulé</option>
                    </x-ui.select>
                </div>
            </div>
        </div>

        <!-- Facturation -->
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">payments</span>
                Facturation
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <x-ui.select
                        label="Type de facturation *"
                        wire:model.live="billing_type"
                        :error="$errors->first('billing_type')"
                    >
                        <option value="non_billable">Non facturable</option>
                        <option value="fixed">Forfait</option>
                        <option value="hourly">Horaire</option>
                    </x-ui.select>
                </div>

                <div>
                    <x-ui.input
                        type="number"
                        step="0.01"
                        label="Budget (FCFA)"
                        wire:model="budget"
                        placeholder="0.00"
                        :error="$errors->first('budget')"
                    />
                </div>

                @if($billing_type === 'hourly')
                    <div>
                        <x-ui.input
                            type="number"
                            step="0.01"
                            label="Taux horaire (FCFA)"
                            wire:model="hourly_rate"
                            placeholder="0.00"
                            :error="$errors->first('hourly_rate')"
                        />
                    </div>
                @endif
            </div>
        </div>

        <!-- Membres du projet -->
        <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">group</span>
                    Membres du projet
                </h3>
            </div>

            <!-- Liste des membres -->
            @if(count($members) > 0)
                <div class="space-y-2 mb-4">
                    @foreach($members as $index => $member)
                        <div class="flex items-center justify-between p-3 bg-surface-highlight rounded-lg border border-[#3a2e24]">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-sm font-medium text-primary">
                                    {{ substr($member['employee_name'] ?? 'XX', 0, 2) }}
                                </div>
                                <div>
                                    <p class="text-white font-medium">{{ $member['employee_name'] ?? '-' }}</p>
                                    <p class="text-sm text-text-secondary">
                                        {{ $member['role'] ?: 'Pas de rôle' }}
                                        @if($member['hourly_rate'])
                                            · {{ number_format($member['hourly_rate'], 2) }} FCFA/h
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <button type="button" wire:click="removeMember({{ $index }})"
                                class="text-red-400 hover:text-red-300 p-1">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Ajouter un membre -->
            <div class="p-4 bg-surface-highlight rounded-lg border border-[#3a2e24]">
                <p class="text-sm text-text-secondary mb-3">Ajouter un membre</p>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <x-ui.select wire:model="newMember.employee_id" :error="$errors->first('newMember.employee_id')">
                            <option value="">Sélectionner un employé</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    <div>
                        <x-ui.input
                            type="text"
                            wire:model="newMember.role"
                            placeholder="Rôle"
                        />
                    </div>
                    <div>
                        <x-ui.input
                            type="number"
                            step="0.01"
                            wire:model="newMember.hourly_rate"
                            placeholder="Taux horaire"
                        />
                    </div>
                    <div>
                        <x-ui.button type="secondary" wire:click="addMember" class="w-full flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">add</span>
                            Ajouter
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
            <x-ui.button type="secondary" href="{{ route('productivity.projects.index') }}">
                Annuler
            </x-ui.button>
            <x-ui.button type="primary" submit class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">save</span>
                {{ $project?->exists ? 'Mettre à jour' : 'Créer le projet' }}
            </x-ui.button>
        </div>
    </form>
</div>
