<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white">
                {{ $employee ? 'Modifier l\'Employe' : 'Nouvel Employe' }}
            </h2>
            <p class="text-text-secondary text-sm">
                {{ $employee ? $employee->full_name : 'Creation d\'un nouveau collaborateur' }}
            </p>
        </div>
        <div class="flex gap-2">
            <x-ui.button href="{{ route('hr.employees.index') }}" type="secondary">
                Annuler
            </x-ui.button>
            <x-ui.button wire:click="save" type="primary" class="shadow-lg shadow-primary/20">
                Enregistrer
            </x-ui.button>
        </div>
    </div>

    <!-- Compte Utilisateur (EN PREMIER pour nouveau employe) -->
    @if(!$employee)
    <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
        <h3 class="text-lg font-bold text-white mb-4 border-b border-[#3a2e24] pb-2">
            <span class="material-symbols-outlined text-primary align-middle mr-2">account_circle</span>
            Compte Utilisateur ERP
        </h3>
        <div class="space-y-3">
            <label class="flex items-center gap-3 p-3 rounded-lg border border-[#3a2e24] cursor-pointer hover:bg-surface-highlight transition-colors {{ $user_action === 'none' ? 'bg-surface-highlight border-primary' : '' }}">
                <input type="radio" wire:model.live="user_action" value="none"
                    class="form-radio h-4 w-4 text-primary border-[#3a2e24] bg-background-dark focus:ring-primary">
                <div>
                    <span class="text-white font-medium">Pas de compte utilisateur</span>
                    <p class="text-xs text-text-secondary">L'employe n'aura pas d'acces a l'ERP - saisie manuelle des informations</p>
                </div>
            </label>

            <label class="flex items-center gap-3 p-3 rounded-lg border border-[#3a2e24] cursor-pointer hover:bg-surface-highlight transition-colors {{ $user_action === 'link' ? 'bg-surface-highlight border-primary' : '' }}">
                <input type="radio" wire:model.live="user_action" value="link"
                    class="form-radio h-4 w-4 text-primary border-[#3a2e24] bg-background-dark focus:ring-primary">
                <div>
                    <span class="text-white font-medium">Lier a un utilisateur existant</span>
                    <p class="text-xs text-text-secondary">Les informations seront recuperees depuis le compte utilisateur</p>
                </div>
            </label>

            @if($user_action === 'link')
                <div class="ml-7 mt-2">
                    <x-ui.select wire:model.live="user_id" label="Selectionner l'utilisateur">
                        <option value="">Choisir un utilisateur...</option>
                        @foreach($availableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </x-ui.select>
                    @error('user_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror

                    @if($user_id)
                        <div class="mt-3 p-3 bg-green-500/10 border border-green-500/30 rounded-lg">
                            <div class="flex items-center gap-2 text-green-400 text-sm">
                                <span class="material-symbols-outlined text-lg">check_circle</span>
                                <span>Utilisateur selectionne - les informations seront pre-remplies</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <label class="flex items-center gap-3 p-3 rounded-lg border border-[#3a2e24] cursor-pointer hover:bg-surface-highlight transition-colors {{ $user_action === 'create' ? 'bg-surface-highlight border-primary' : '' }}">
                <input type="radio" wire:model.live="user_action" value="create"
                    class="form-radio h-4 w-4 text-primary border-[#3a2e24] bg-background-dark focus:ring-primary">
                <div>
                    <span class="text-white font-medium">Creer un nouveau compte</span>
                    <p class="text-xs text-text-secondary">Un compte sera cree avec les informations saisies (mot de passe: password)</p>
                </div>
            </label>
        </div>
    </x-ui.card>
    @endif

    <!-- Personal Info -->
    <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
        <h3 class="text-lg font-bold text-white mb-4 border-b border-[#3a2e24] pb-2">Informations Personnelles</h3>

        @if($user_action === 'link' && $user_id)
            <div class="mb-4 p-3 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                <div class="flex items-center gap-2 text-blue-400 text-sm">
                    <span class="material-symbols-outlined text-lg">info</span>
                    <span>Nom et email recuperes depuis le compte utilisateur</span>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-ui.input
                wire:model="first_name"
                label="Prenom"
                required
                :readonly="$user_action === 'link' && $user_id"
                placeholder="Prenom de l'employe"
                :error="$errors->first('first_name')" />

            <x-ui.input
                wire:model="last_name"
                label="Nom"
                required
                :readonly="$user_action === 'link' && $user_id"
                placeholder="Nom de l'employe"
                :error="$errors->first('last_name')" />

            <x-ui.input
                wire:model="email"
                type="email"
                label="Email Professionnel"
                required
                :readonly="$user_action === 'link' && $user_id"
                placeholder="email@entreprise.com"
                :error="$errors->first('email')" />

            <x-ui.input wire:model="phone" label="Telephone" placeholder="+225 00 00 00 00" />
            <x-ui.input wire:model="birth_date" type="date" label="Date de Naissance" />
        </div>
    </x-ui.card>

    <!-- Job Info -->
    <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
        <h3 class="text-lg font-bold text-white mb-4 border-b border-[#3a2e24] pb-2">Informations Poste</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-ui.input wire:model="job_title" label="Intitule du Poste" required placeholder="Ex: Developpeur Senior" />
            <x-ui.select wire:model="department_id" label="Departement" required>
                <option value="">Selectionner...</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select wire:model="manager_id" label="Manager (N+1)">
                <option value="">Aucun</option>
                @foreach($managers as $mgr)
                    <option value="{{ $mgr->id }}">{{ $mgr->full_name }}</option>
                @endforeach
            </x-ui.select>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input wire:model="hire_date" type="date" label="Date d'embauche" required />
                <x-ui.input wire:model="end_date" type="date" label="Fin de contrat" />
            </div>

            <x-ui.select wire:model="contract_type" label="Type de Contrat" required>
                <option value="cdi">CDI</option>
                <option value="cdd">CDD</option>
                <option value="interim">Interim</option>
                <option value="stage">Stage</option>
                <option value="alternance">Alternance</option>
            </x-ui.select>

            <x-ui.select wire:model="status" label="Statut" required>
                <option value="active">Actif</option>
                <option value="inactive">Inactif</option>
                <option value="terminated">Termine</option>
            </x-ui.select>
        </div>
    </x-ui.card>

    <!-- Administrative -->
    <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
        <h3 class="text-lg font-bold text-white mb-4 border-b border-[#3a2e24] pb-2">Informations Administratives</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-ui.input wire:model="salary" type="number" step="0.01" label="Salaire Mensuel Brut (FCFA)" placeholder="Ex: 500000" />

            <!-- User Account for existing employee -->
            @if($employee)
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Compte Utilisateur ERP</label>
                    @if($employee->user_id)
                        <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-green-500">link</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-white font-medium">{{ $employee->user->name }}</p>
                                    <p class="text-sm text-text-secondary">{{ $employee->user->email }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4">
                            <div class="flex items-center gap-2 text-yellow-400">
                                <span class="material-symbols-outlined">warning</span>
                                <span class="text-sm">Aucun compte utilisateur lie</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </x-ui.card>
</div>
