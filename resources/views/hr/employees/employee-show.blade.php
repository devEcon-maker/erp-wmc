<div class="space-y-6">
    <!-- Header avec photo et infos principales -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl overflow-hidden">
        <div class="h-24 bg-gradient-to-r from-primary/30 via-primary/20 to-primary/10"></div>
        <div class="px-6 pb-6">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between -mt-12">
                <div class="flex items-end gap-4">
                    @if($employee->photo_url)
                        <img src="{{ $employee->photo_url }}" alt="{{ $employee->full_name }}"
                            class="size-24 rounded-2xl border-4 border-surface-dark object-cover shadow-xl">
                    @else
                        <div class="size-24 rounded-2xl bg-primary/20 flex items-center justify-center text-2xl font-bold text-white border-4 border-surface-dark shadow-xl">
                            {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                        </div>
                    @endif
                    <div class="pb-1">
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold text-white">{{ $employee->full_name }}</h1>
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $employee->status === 'active' ? 'bg-green-500/20 text-green-400 border border-green-500/30' : '' }}
                                {{ $employee->status === 'inactive' ? 'bg-gray-500/20 text-gray-400 border border-gray-500/30' : '' }}
                                {{ $employee->status === 'on_leave' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : '' }}
                                {{ $employee->status === 'terminated' ? 'bg-red-500/20 text-red-400 border border-red-500/30' : '' }}">
                                {{ $employee->status_label }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3 text-text-secondary mt-1">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">badge</span>
                                {{ $employee->job_title }}
                            </span>
                            <span class="text-[#3a2e24]">|</span>
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">business</span>
                                {{ $employee->department->name ?? 'Non assigne' }}
                            </span>
                            <span class="text-[#3a2e24]">|</span>
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">tag</span>
                                {{ $employee->employee_number }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2 mt-4 md:mt-0">
                    <x-ui.button href="{{ route('hr.employees.index') }}" type="secondary">
                        <span class="material-symbols-outlined text-[18px] mr-1">arrow_back</span>
                        Retour
                    </x-ui.button>
                    @can('employees.edit')
                    <x-ui.button href="{{ route('hr.employees.edit', $employee) }}" type="primary">
                        <span class="material-symbols-outlined text-[18px] mr-1">edit</span>
                        Modifier
                    </x-ui.button>
                    @endcan
                    @can('employees.delete')
                    <button wire:click="confirmDelete"
                        class="px-4 py-2 rounded-xl text-red-400 hover:text-white hover:bg-red-500/20 border border-red-500/20 font-bold transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                        Supprimer
                    </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-1.5">
        <div class="flex gap-1">
            @foreach([
                'info' => ['icon' => 'person', 'label' => 'Informations'],
                'leaves' => ['icon' => 'event_busy', 'label' => 'Conges'],
                'expenses' => ['icon' => 'receipt_long', 'label' => 'Notes de Frais'],
                'timesheets' => ['icon' => 'schedule', 'label' => 'Feuilles de Temps']
            ] as $key => $tab)
                <button wire:click="setTab('{{ $key }}')"
                    class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-sm font-medium transition-all
                    {{ $activeTab === $key
                        ? 'bg-primary text-white shadow-lg shadow-primary/20'
                        : 'text-text-secondary hover:text-white hover:bg-surface-highlight' }}">
                    <span class="material-symbols-outlined text-[20px]">{{ $tab['icon'] }}</span>
                    <span class="hidden sm:inline">{{ $tab['label'] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Tab Content -->
    <div class="min-h-[400px]">
        <!-- ===================== ONGLET INFORMATIONS ===================== -->
        @if($activeTab === 'info')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Colonne principale -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Informations Professionnelles -->
                    <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">work</span>
                            Informations Professionnelles
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <div class="text-xs text-text-secondary uppercase tracking-wider">Matricule</div>
                                <div class="text-white font-mono text-lg">{{ $employee->employee_number }}</div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-xs text-text-secondary uppercase tracking-wider">Email Professionnel</div>
                                <div class="text-white">{{ $employee->email }}</div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-xs text-text-secondary uppercase tracking-wider">Telephone</div>
                                <div class="text-white">{{ $employee->phone ?? '-' }}</div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-xs text-text-secondary uppercase tracking-wider">Date d'embauche</div>
                                <div class="text-white">{{ $employee->hire_date?->format('d/m/Y') ?? '-' }}</div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-xs text-text-secondary uppercase tracking-wider">Anciennete</div>
                                <div class="text-white">{{ $employee->seniority_years }} an(s)</div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-xs text-text-secondary uppercase tracking-wider">Type de Contrat</div>
                                <div class="text-white">
                                    <span class="px-2 py-1 rounded-lg bg-primary/10 text-primary text-sm font-medium">
                                        {{ strtoupper($employee->contract_type ?? 'N/A') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations Personnelles -->
                    <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">person</span>
                            Informations Personnelles
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <div class="text-xs text-text-secondary uppercase tracking-wider">Date de naissance</div>
                                <div class="text-white">{{ $employee->birth_date?->format('d/m/Y') ?? '-' }}
                                    @if($employee->age)
                                        <span class="text-text-secondary">({{ $employee->age }} ans)</span>
                                    @endif
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-xs text-text-secondary uppercase tracking-wider">Genre</div>
                                <div class="text-white">{{ $employee->gender_label ?? '-' }}</div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-xs text-text-secondary uppercase tracking-wider">Nationalite</div>
                                <div class="text-white">{{ $employee->nationality ?? '-' }}</div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-xs text-text-secondary uppercase tracking-wider">Situation familiale</div>
                                <div class="text-white">{{ $employee->marital_status_label ?? '-' }}</div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-xs text-text-secondary uppercase tracking-wider">Personnes a charge</div>
                                <div class="text-white">{{ $employee->dependents_count ?? 0 }}</div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-xs text-text-secondary uppercase tracking-wider">Adresse</div>
                                <div class="text-white">{{ $employee->address ?? '-' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Documents & Contact d'urgence -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Documents -->
                        <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6">
                            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">folder</span>
                                Documents
                            </h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-3 rounded-xl bg-surface-highlight border border-[#3a2e24]">
                                    <div>
                                        <div class="text-sm text-white font-medium">CNI</div>
                                        <div class="text-xs text-text-secondary">{{ $employee->cni_number ?? 'Non renseigne' }}</div>
                                    </div>
                                    @if($employee->cni_expiry_date)
                                        <span class="text-xs {{ $employee->is_cni_expiring ? 'text-amber-400' : 'text-text-secondary' }}">
                                            Exp: {{ $employee->cni_expiry_date->format('d/m/Y') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between p-3 rounded-xl bg-surface-highlight border border-[#3a2e24]">
                                    <div>
                                        <div class="text-sm text-white font-medium">Securite Sociale</div>
                                        <div class="text-xs text-text-secondary">{{ $employee->social_security_number ?? 'Non renseigne' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact d'urgence -->
                        <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6">
                            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">emergency</span>
                                Contact d'urgence
                            </h3>
                            @if($employee->emergency_contact_name)
                                <div class="space-y-3">
                                    <div class="flex items-center gap-3">
                                        <div class="size-10 rounded-full bg-red-500/20 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-red-400">person</span>
                                        </div>
                                        <div>
                                            <div class="text-white font-medium">{{ $employee->emergency_contact_name }}</div>
                                            <div class="text-xs text-text-secondary">{{ $employee->emergency_contact_relationship ?? 'Relation non precisee' }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 text-text-secondary">
                                        <span class="material-symbols-outlined text-[18px]">phone</span>
                                        <span>{{ $employee->emergency_contact_phone }}</span>
                                    </div>
                                </div>
                            @else
                                <p class="text-text-secondary text-sm italic">Aucun contact d'urgence renseigne</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Colonne laterale -->
                <div class="space-y-6">
                    <!-- Hierarchie -->
                    <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6">
                        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">account_tree</span>
                            Hierarchie
                        </h3>
                        <div class="space-y-6">
                            <!-- Manager -->
                            <div>
                                <div class="text-xs text-text-secondary uppercase tracking-wider mb-2">Manager (N+1)</div>
                                @if($employee->manager)
                                    <a href="{{ route('hr.employees.show', $employee->manager) }}"
                                        class="flex items-center gap-3 p-3 rounded-xl bg-surface-highlight border border-[#3a2e24] hover:border-primary/50 transition-colors group">
                                        <div class="size-10 rounded-full bg-primary/20 flex items-center justify-center text-xs font-bold text-white">
                                            {{ substr($employee->manager->first_name, 0, 1) }}{{ substr($employee->manager->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-white group-hover:text-primary transition-colors">
                                                {{ $employee->manager->full_name }}
                                            </div>
                                            <div class="text-xs text-text-secondary">{{ $employee->manager->job_title }}</div>
                                        </div>
                                    </a>
                                @else
                                    <div class="text-sm text-text-secondary italic">Aucun manager direct</div>
                                @endif
                            </div>

                            <!-- Collaborateurs -->
                            <div>
                                <div class="text-xs text-text-secondary uppercase tracking-wider mb-2">
                                    Collaborateurs (N-1)
                                    @if($employee->directReports->count() > 0)
                                        <span class="ml-1 px-1.5 py-0.5 rounded-full bg-primary/20 text-primary text-[10px]">
                                            {{ $employee->directReports->count() }}
                                        </span>
                                    @endif
                                </div>
                                @if($employee->directReports->count() > 0)
                                    <div class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar">
                                        @foreach($employee->directReports as $report)
                                            <a href="{{ route('hr.employees.show', $report) }}"
                                                class="flex items-center gap-3 p-2 rounded-lg hover:bg-surface-highlight transition-colors">
                                                <div class="size-8 rounded-full bg-surface-highlight border border-[#3a2e24] flex items-center justify-center text-[10px] font-bold text-text-secondary">
                                                    {{ substr($report->first_name, 0, 1) }}{{ substr($report->last_name, 0, 1) }}
                                                </div>
                                                <div class="text-sm text-text-secondary hover:text-white">{{ $report->full_name }}</div>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-sm text-text-secondary italic">Aucun collaborateur</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Informations Bancaires -->
                    <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6">
                        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">account_balance</span>
                            Informations Bancaires
                        </h3>
                        @if($employee->bank_name)
                            <div class="space-y-4">
                                <div class="space-y-1">
                                    <div class="text-xs text-text-secondary uppercase tracking-wider">Banque</div>
                                    <div class="text-white">{{ $employee->bank_name }}</div>
                                </div>
                                <div class="space-y-1">
                                    <div class="text-xs text-text-secondary uppercase tracking-wider">Numero de compte</div>
                                    <div class="text-white font-mono text-sm">{{ $employee->bank_account_number ?? '-' }}</div>
                                </div>
                                @if($employee->bank_rib)
                                    <div class="space-y-1">
                                        <div class="text-xs text-text-secondary uppercase tracking-wider">RIB</div>
                                        <div class="text-white font-mono text-sm">{{ $employee->bank_rib }}</div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-text-secondary text-sm italic">Non renseigne</p>
                        @endif
                    </div>

                    <!-- Compte utilisateur -->
                    @if($employee->user)
                        <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6">
                            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">account_circle</span>
                                Compte Utilisateur
                            </h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-text-secondary text-sm">Email</span>
                                    <span class="text-white text-sm">{{ $employee->user->email }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-text-secondary text-sm">Statut</span>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $employee->user->is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                        {{ $employee->user->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-text-secondary text-sm">Derniere connexion</span>
                                    <span class="text-white text-sm">{{ $employee->user->last_login_at?->diffForHumans() ?? 'Jamais' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        <!-- ===================== ONGLET CONGES ===================== -->
        @elseif($activeTab === 'leaves')
            <div class="space-y-6">
                <!-- Filtres et stats -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <select wire:model.live="leaveYear"
                            class="bg-surface-dark border border-[#3a2e24] rounded-xl px-4 py-2 text-white text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                            @for($y = now()->year; $y >= now()->year - 3; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                        <select wire:model.live="leaveStatus"
                            class="bg-surface-dark border border-[#3a2e24] rounded-xl px-4 py-2 text-white text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                            <option value="">Tous les statuts</option>
                            <option value="pending">En attente</option>
                            <option value="approved">Approuve</option>
                            <option value="rejected">Refuse</option>
                        </select>
                    </div>
                    <a href="{{ route('hr.leaves.requests.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Nouvelle demande
                    </a>
                </div>

                <!-- Stats cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-xl bg-blue-500/20 flex items-center justify-center">
                                <span class="material-symbols-outlined text-blue-400">description</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white">{{ $leaveStats['total'] }}</div>
                                <div class="text-xs text-text-secondary">Demandes</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-xl bg-amber-500/20 flex items-center justify-center">
                                <span class="material-symbols-outlined text-amber-400">pending</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white">{{ $leaveStats['pending'] }}</div>
                                <div class="text-xs text-text-secondary">En attente</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-xl bg-green-500/20 flex items-center justify-center">
                                <span class="material-symbols-outlined text-green-400">check_circle</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white">{{ $leaveStats['approved'] }}</div>
                                <div class="text-xs text-text-secondary">Approuvees</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-xl bg-primary/20 flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary">event_available</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white">{{ $leaveStats['days_taken'] }}</div>
                                <div class="text-xs text-text-secondary">Jours pris</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Soldes de conges -->
                <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">account_balance_wallet</span>
                        Soldes de conges {{ $leaveYear }}
                    </h3>
                    @if($leaveBalances->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($leaveBalances as $balance)
                                <div class="p-4 rounded-xl bg-surface-highlight border border-[#3a2e24]">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-white font-medium">{{ $balance->leaveType->name }}</span>
                                        <div class="size-3 rounded-full" style="background-color: {{ $balance->leaveType->color ?? '#f48c25' }}"></div>
                                    </div>
                                    <div class="flex items-end justify-between">
                                        <div>
                                            <div class="text-3xl font-bold text-white">{{ $balance->remaining }}</div>
                                            <div class="text-xs text-text-secondary">jours restants</div>
                                        </div>
                                        <div class="text-right text-sm">
                                            <div class="text-text-secondary">{{ $balance->used }} / {{ $balance->allocated }}</div>
                                            <div class="text-xs text-text-secondary">utilises</div>
                                        </div>
                                    </div>
                                    <div class="mt-3 h-2 bg-surface-dark rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all"
                                            style="width: {{ $balance->allocated > 0 ? ($balance->used / $balance->allocated) * 100 : 0 }}%;
                                                   background-color: {{ $balance->leaveType->color ?? '#f48c25' }}"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-text-secondary text-sm italic">Aucun solde configure pour cette annee</p>
                    @endif
                </div>

                <!-- Historique des demandes -->
                <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl overflow-hidden">
                    <div class="p-6 border-b border-[#3a2e24]">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">history</span>
                            Historique des demandes
                        </h3>
                    </div>
                    @if($leaveRequests->count() > 0)
                        <div class="divide-y divide-[#3a2e24]">
                            @foreach($leaveRequests as $request)
                                <div class="p-4 hover:bg-surface-highlight/50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="size-10 rounded-xl flex items-center justify-center"
                                                style="background-color: {{ $request->leaveType->color ?? '#f48c25' }}20">
                                                <span class="material-symbols-outlined" style="color: {{ $request->leaveType->color ?? '#f48c25' }}">event_busy</span>
                                            </div>
                                            <div>
                                                <div class="text-white font-medium">{{ $request->leaveType->name }}</div>
                                                <div class="text-sm text-text-secondary">
                                                    {{ $request->start_date->format('d/m/Y') }}
                                                    @if($request->start_date->ne($request->end_date))
                                                        - {{ $request->end_date->format('d/m/Y') }}
                                                    @endif
                                                    <span class="text-primary">({{ $request->days_count }} jour(s))</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                                {{ $request->status === 'pending' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : '' }}
                                                {{ $request->status === 'approved' ? 'bg-green-500/20 text-green-400 border border-green-500/30' : '' }}
                                                {{ $request->status === 'rejected' ? 'bg-red-500/20 text-red-400 border border-red-500/30' : '' }}">
                                                {{ $request->status === 'pending' ? 'En attente' : ($request->status === 'approved' ? 'Approuve' : 'Refuse') }}
                                            </span>
                                            @if($request->approver)
                                                <div class="text-xs text-text-secondary mt-1">
                                                    par {{ $request->approver->full_name }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    @if($request->reason)
                                        <div class="mt-2 ml-14 text-sm text-text-secondary italic">
                                            "{{ $request->reason }}"
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="p-4 border-t border-[#3a2e24]">
                            {{ $leaveRequests->links() }}
                        </div>
                    @else
                        <div class="p-12 text-center">
                            <span class="material-symbols-outlined text-4xl text-text-secondary opacity-50">event_busy</span>
                            <p class="mt-2 text-text-secondary">Aucune demande de conge pour cette periode</p>
                        </div>
                    @endif
                </div>
            </div>

        <!-- ===================== ONGLET NOTES DE FRAIS ===================== -->
        @elseif($activeTab === 'expenses')
            <div class="space-y-6">
                <!-- Filtres -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <select wire:model.live="expenseStatus"
                        class="bg-surface-dark border border-[#3a2e24] rounded-xl px-4 py-2 text-white text-sm focus:border-primary focus:ring-1 focus:ring-primary w-auto">
                        <option value="">Tous les statuts</option>
                        <option value="draft">Brouillon</option>
                        <option value="submitted">Soumise</option>
                        <option value="approved">Approuvee</option>
                        <option value="rejected">Rejetee</option>
                        <option value="paid">Payee</option>
                    </select>
                    <a href="{{ route('hr.expenses.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Nouvelle note
                    </a>
                </div>

                <!-- Stats cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-xl bg-blue-500/20 flex items-center justify-center">
                                <span class="material-symbols-outlined text-blue-400">receipt_long</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white">{{ $expenseStats['total_reports'] }}</div>
                                <div class="text-xs text-text-secondary">Notes {{ now()->year }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-xl bg-amber-500/20 flex items-center justify-center">
                                <span class="material-symbols-outlined text-amber-400">pending</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white">{{ number_format($expenseStats['pending_amount'], 0, ',', ' ') }}</div>
                                <div class="text-xs text-text-secondary">FCFA en attente</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-xl bg-green-500/20 flex items-center justify-center">
                                <span class="material-symbols-outlined text-green-400">check_circle</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white">{{ number_format($expenseStats['approved_amount'], 0, ',', ' ') }}</div>
                                <div class="text-xs text-text-secondary">FCFA approuves</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-xl bg-primary/20 flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary">payments</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white">{{ number_format($expenseStats['paid_amount'], 0, ',', ' ') }}</div>
                                <div class="text-xs text-text-secondary">FCFA rembourses</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des notes de frais -->
                <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl overflow-hidden">
                    <div class="p-6 border-b border-[#3a2e24]">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">folder_open</span>
                            Notes de frais
                        </h3>
                    </div>
                    @if($expenseReports->count() > 0)
                        <div class="divide-y divide-[#3a2e24]">
                            @foreach($expenseReports as $report)
                                <a href="{{ route('hr.expenses.show', $report) }}"
                                    class="block p-4 hover:bg-surface-highlight/50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="size-10 rounded-xl bg-primary/20 flex items-center justify-center">
                                                <span class="material-symbols-outlined text-primary">receipt_long</span>
                                            </div>
                                            <div>
                                                <div class="text-white font-medium">{{ $report->reference }}</div>
                                                <div class="text-sm text-text-secondary">
                                                    {{ $report->period_start?->format('d/m/Y') }} - {{ $report->period_end?->format('d/m/Y') }}
                                                    <span class="text-text-secondary/60">({{ $report->lines->count() }} ligne(s))</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-bold text-white">{{ number_format($report->total_amount, 0, ',', ' ') }} FCFA</div>
                                            <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                                {{ $report->status === 'draft' ? 'bg-gray-500/20 text-gray-400 border border-gray-500/30' : '' }}
                                                {{ $report->status === 'submitted' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : '' }}
                                                {{ $report->status === 'approved' ? 'bg-green-500/20 text-green-400 border border-green-500/30' : '' }}
                                                {{ $report->status === 'rejected' ? 'bg-red-500/20 text-red-400 border border-red-500/30' : '' }}
                                                {{ $report->status === 'paid' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : '' }}">
                                                {{ $report->status_label }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        <div class="p-4 border-t border-[#3a2e24]">
                            {{ $expenseReports->links() }}
                        </div>
                    @else
                        <div class="p-12 text-center">
                            <span class="material-symbols-outlined text-4xl text-text-secondary opacity-50">receipt_long</span>
                            <p class="mt-2 text-text-secondary">Aucune note de frais</p>
                        </div>
                    @endif
                </div>
            </div>

        <!-- ===================== ONGLET FEUILLES DE TEMPS ===================== -->
        @elseif($activeTab === 'timesheets')
            <div class="space-y-6">
                <!-- Filtres -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <select wire:model.live="timesheetMonth"
                            class="bg-surface-dark border border-[#3a2e24] rounded-xl px-4 py-2 text-white text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                            @foreach(['Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre'] as $index => $month)
                                <option value="{{ $index + 1 }}">{{ $month }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="timesheetYear"
                            class="bg-surface-dark border border-[#3a2e24] rounded-xl px-4 py-2 text-white text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                            @for($y = now()->year; $y >= now()->year - 2; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <a href="{{ route('hr.timesheets.daily') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Saisir du temps
                    </a>
                </div>

                <!-- Stats cards -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-xl bg-blue-500/20 flex items-center justify-center">
                                <span class="material-symbols-outlined text-blue-400">schedule</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white">{{ number_format($timesheetStats['month_hours'], 1) }}h</div>
                                <div class="text-xs text-text-secondary">Ce mois</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-xl bg-green-500/20 flex items-center justify-center">
                                <span class="material-symbols-outlined text-green-400">attach_money</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white">{{ number_format($timesheetStats['month_billable'], 1) }}h</div>
                                <div class="text-xs text-text-secondary">Facturable</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-xl bg-amber-500/20 flex items-center justify-center">
                                <span class="material-symbols-outlined text-amber-400">check_circle</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white">{{ number_format($timesheetStats['month_approved'], 1) }}h</div>
                                <div class="text-xs text-text-secondary">Approuve</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-xl bg-purple-500/20 flex items-center justify-center">
                                <span class="material-symbols-outlined text-purple-400">calendar_month</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white">{{ number_format($timesheetStats['year_hours'], 1) }}h</div>
                                <div class="text-xs text-text-secondary">Cette annee</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-surface-dark border border-[#3a2e24] rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-xl bg-primary/20 flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary">trending_up</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white">{{ number_format($timesheetStats['year_billable'], 1) }}h</div>
                                <div class="text-xs text-text-secondary">Annee facturable</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feuilles de temps par jour -->
                <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl overflow-hidden">
                    <div class="p-6 border-b border-[#3a2e24]">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">view_timeline</span>
                            Detail du temps saisi
                        </h3>
                    </div>
                    @if($timesheets->count() > 0)
                        <div class="divide-y divide-[#3a2e24]">
                            @foreach($timesheetsByDay as $date => $dayTimesheets)
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="size-10 rounded-xl bg-surface-highlight flex items-center justify-center">
                                                <span class="text-white font-bold">{{ \Carbon\Carbon::parse($date)->format('d') }}</span>
                                            </div>
                                            <div>
                                                <div class="text-white font-medium">{{ \Carbon\Carbon::parse($date)->translatedFormat('l') }}</div>
                                                <div class="text-xs text-text-secondary">{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-bold text-primary">{{ number_format($dayTimesheets->sum('hours'), 1) }}h</div>
                                            <div class="text-xs text-text-secondary">
                                                {{ number_format($dayTimesheets->where('billable', true)->sum('hours'), 1) }}h facturable
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ml-13 space-y-2">
                                        @foreach($dayTimesheets as $entry)
                                            <div class="flex items-center justify-between p-3 rounded-xl bg-surface-highlight border border-[#3a2e24]">
                                                <div class="flex items-center gap-3">
                                                    <div class="size-8 rounded-lg {{ $entry->billable ? 'bg-green-500/20' : 'bg-gray-500/20' }} flex items-center justify-center">
                                                        <span class="material-symbols-outlined text-[16px] {{ $entry->billable ? 'text-green-400' : 'text-gray-400' }}">
                                                            {{ $entry->billable ? 'attach_money' : 'money_off' }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm text-white">
                                                            {{ $entry->project?->name ?? 'Sans projet' }}
                                                            @if($entry->task)
                                                                <span class="text-text-secondary">/ {{ $entry->task->name }}</span>
                                                            @endif
                                                        </div>
                                                        @if($entry->description)
                                                            <div class="text-xs text-text-secondary">{{ Str::limit($entry->description, 60) }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <span class="text-white font-medium">{{ number_format($entry->hours, 1) }}h</span>
                                                    @if($entry->approved)
                                                        <span class="size-6 rounded-full bg-green-500/20 flex items-center justify-center">
                                                            <span class="material-symbols-outlined text-green-400 text-[14px]">check</span>
                                                        </span>
                                                    @else
                                                        <span class="size-6 rounded-full bg-amber-500/20 flex items-center justify-center">
                                                            <span class="material-symbols-outlined text-amber-400 text-[14px]">pending</span>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-12 text-center">
                            <span class="material-symbols-outlined text-4xl text-text-secondary opacity-50">schedule</span>
                            <p class="mt-2 text-text-secondary">Aucune entree de temps pour cette periode</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Modal de confirmation de suppression -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl p-6 max-w-md w-full shadow-xl">
                <div class="flex items-center gap-3 mb-4">
                    <div class="size-12 rounded-full bg-red-500/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-red-400 text-2xl">warning</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Confirmer la suppression</h3>
                        <p class="text-text-secondary text-sm">Cette action est irreversible</p>
                    </div>
                </div>
                <p class="text-text-secondary mb-6">
                    Etes-vous sur de vouloir supprimer l'employe <strong class="text-white">{{ $employee->full_name }}</strong> ?
                    <br><br>
                    <span class="text-xs">Poste: {{ $employee->job_title }}</span>
                </p>
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete"
                        class="px-4 py-2 rounded-xl border border-[#3a2e24] text-text-secondary hover:text-white hover:bg-surface-highlight font-medium transition-colors">
                        Annuler
                    </button>
                    <button wire:click="deleteEmployee"
                        class="px-4 py-2 rounded-xl bg-red-500 text-white hover:bg-red-600 font-medium transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
