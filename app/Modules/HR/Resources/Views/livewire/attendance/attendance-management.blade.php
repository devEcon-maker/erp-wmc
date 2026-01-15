<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Gestion des Presences</h1>
        <p class="text-text-secondary mt-1">Gerez les pointages, retards, absences et permissions</p>
    </div>

    <!-- Onglets -->
    <div class="flex gap-2 mb-6 border-b border-white/10">
        <button wire:click="setTab('attendances')"
            class="px-4 py-2 text-sm font-medium {{ $activeTab === 'attendances' ? 'text-primary border-b-2 border-primary' : 'text-text-secondary hover:text-white' }}">
            Pointages
        </button>
        <button wire:click="setTab('late')"
            class="px-4 py-2 text-sm font-medium {{ $activeTab === 'late' ? 'text-primary border-b-2 border-primary' : 'text-text-secondary hover:text-white' }}">
            Retards
            @if($lateArrivals->total() > 0)
                <span class="ml-1 px-1.5 py-0.5 bg-amber-500/20 text-amber-400 text-xs rounded-full">{{ $lateArrivals->total() }}</span>
            @endif
        </button>
        <button wire:click="setTab('absences')"
            class="px-4 py-2 text-sm font-medium {{ $activeTab === 'absences' ? 'text-primary border-b-2 border-primary' : 'text-text-secondary hover:text-white' }}">
            Absences
            @if($absences->total() > 0)
                <span class="ml-1 px-1.5 py-0.5 bg-red-500/20 text-red-400 text-xs rounded-full">{{ $absences->total() }}</span>
            @endif
        </button>
        <button wire:click="setTab('permissions')"
            class="px-4 py-2 text-sm font-medium {{ $activeTab === 'permissions' ? 'text-primary border-b-2 border-primary' : 'text-text-secondary hover:text-white' }}">
            Permissions
            @if($permissions->total() > 0)
                <span class="ml-1 px-1.5 py-0.5 bg-blue-500/20 text-blue-400 text-xs rounded-full">{{ $permissions->total() }}</span>
            @endif
        </button>
    </div>

    <!-- Tab Pointages -->
    @if($activeTab === 'attendances')
        <div class="bg-surface-dark border border-white/10 rounded-lg overflow-hidden">
            <div class="p-4 border-b border-white/10 flex flex-wrap items-center gap-4">
                <input type="date" wire:model.live="selectedDate"
                    class="bg-surface-darker border border-white/10 rounded-lg text-white px-3 py-2">
                <select wire:model.live="selectedDepartment"
                    class="bg-surface-darker border border-white/10 rounded-lg text-white px-3 py-2">
                    <option value="">Tous les departements</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="statusFilter"
                    class="bg-surface-darker border border-white/10 rounded-lg text-white px-3 py-2">
                    <option value="">Tous les statuts</option>
                    <option value="present">Present</option>
                    <option value="late">En retard</option>
                    <option value="absent">Absent</option>
                    <option value="leave">En conge</option>
                </select>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..."
                    class="bg-surface-darker border border-white/10 rounded-lg text-white px-3 py-2">
                <button wire:click="openManualModal"
                    class="ml-auto px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">add</span>
                    Pointage manuel
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-surface-darker">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Employe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Departement</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Entree</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Sortie</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Heures</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($attendances as $att)
                            <tr class="hover:bg-white/5">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-primary text-sm">
                                            {{ substr($att->employee->first_name, 0, 1) }}{{ substr($att->employee->last_name, 0, 1) }}
                                        </div>
                                        <span class="text-white">{{ $att->employee->full_name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-text-secondary">{{ $att->employee->department?->name ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $att->status_color }}-500/20 text-{{ $att->status_color }}-400">
                                        {{ $att->status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-white">{{ $att->formatted_check_in ?? '-' }}</td>
                                <td class="px-6 py-4 text-white">{{ $att->formatted_check_out ?? '-' }}</td>
                                <td class="px-6 py-4 text-white">{{ $att->formatted_worked_hours ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-text-secondary">
                                    Aucun pointage trouve
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-white/10">
                {{ $attendances->links() }}
            </div>
        </div>
    @endif

    <!-- Tab Retards -->
    @if($activeTab === 'late')
        <div class="bg-surface-dark border border-white/10 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-surface-darker">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Employe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Heure prevue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Heure reelle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Retard</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($lateArrivals as $late)
                            <tr class="hover:bg-white/5">
                                <td class="px-6 py-4 text-white">{{ $late->date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-white">{{ $late->employee->full_name }}</td>
                                <td class="px-6 py-4 text-text-secondary">{{ $late->formatted_expected_time }}</td>
                                <td class="px-6 py-4 text-white">{{ $late->formatted_actual_time }}</td>
                                <td class="px-6 py-4">
                                    <span class="text-amber-400 font-medium">{{ $late->formatted_late_time }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <button wire:click="openLateModal({{ $late->id }})"
                                        class="px-3 py-1 bg-amber-500/20 text-amber-400 rounded hover:bg-amber-500/30 text-sm">
                                        Traiter
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-text-secondary">
                                    Aucun retard en attente
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-white/10">
                {{ $lateArrivals->links() }}
            </div>
        </div>
    @endif

    <!-- Tab Absences -->
    @if($activeTab === 'absences')
        <div class="bg-surface-dark border border-white/10 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-surface-darker">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Employe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Periode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Jours</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Motif</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($absences as $absence)
                            <tr class="hover:bg-white/5">
                                <td class="px-6 py-4 text-white">{{ $absence->employee->full_name }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $absence->type_color }}-500/20 text-{{ $absence->type_color }}-400">
                                        {{ $absence->type_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-text-secondary">
                                    {{ $absence->start_date->format('d/m') }} - {{ $absence->end_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-white">{{ $absence->days_count }}</td>
                                <td class="px-6 py-4 text-text-secondary text-sm">{{ Str::limit($absence->reason, 30) }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <button wire:click="approveAbsence({{ $absence->id }})"
                                            class="p-1 text-green-400 hover:bg-green-500/20 rounded">
                                            <span class="material-symbols-outlined text-sm">check</span>
                                        </button>
                                        <button wire:click="rejectAbsence({{ $absence->id }})"
                                            class="p-1 text-red-400 hover:bg-red-500/20 rounded">
                                            <span class="material-symbols-outlined text-sm">close</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-text-secondary">
                                    Aucune absence en attente
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-white/10">
                {{ $absences->links() }}
            </div>
        </div>
    @endif

    <!-- Tab Permissions -->
    @if($activeTab === 'permissions')
        <div class="bg-surface-dark border border-white/10 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-surface-darker">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Employe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Horaire</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Duree</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($permissions as $perm)
                            <tr class="hover:bg-white/5">
                                <td class="px-6 py-4 text-white">{{ $perm->employee->full_name }}</td>
                                <td class="px-6 py-4 text-text-secondary">{{ $perm->type_label }}</td>
                                <td class="px-6 py-4 text-white">{{ $perm->date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-text-secondary">{{ $perm->formatted_time_range }}</td>
                                <td class="px-6 py-4 text-white">{{ $perm->formatted_hours }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <button wire:click="approvePermission({{ $perm->id }})"
                                            class="p-1 text-green-400 hover:bg-green-500/20 rounded">
                                            <span class="material-symbols-outlined text-sm">check</span>
                                        </button>
                                        <button wire:click="rejectPermission({{ $perm->id }})"
                                            class="p-1 text-red-400 hover:bg-red-500/20 rounded">
                                            <span class="material-symbols-outlined text-sm">close</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-text-secondary">
                                    Aucune permission en attente
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-white/10">
                {{ $permissions->links() }}
            </div>
        </div>
    @endif

    <!-- Modal Pointage Manuel -->
    @if($showManualModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/60" wire:click="$set('showManualModal', false)"></div>
                <div class="relative bg-surface-dark border border-white/10 rounded-lg w-full max-w-md p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Pointage manuel</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-text-secondary mb-1">Employe *</label>
                            <select wire:model="manualEmployeeId"
                                class="w-full bg-surface-darker border border-white/10 rounded-lg text-white px-3 py-2">
                                <option value="">Selectionner...</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-secondary mb-1">Date *</label>
                            <input type="date" wire:model="manualDate"
                                class="w-full bg-surface-darker border border-white/10 rounded-lg text-white px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-secondary mb-1">Statut *</label>
                            <select wire:model="manualStatus"
                                class="w-full bg-surface-darker border border-white/10 rounded-lg text-white px-3 py-2">
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="leave">En conge</option>
                                <option value="remote">Teletravail</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-text-secondary mb-1">Heure entree</label>
                                <input type="time" wire:model="manualCheckIn"
                                    class="w-full bg-surface-darker border border-white/10 rounded-lg text-white px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-text-secondary mb-1">Heure sortie</label>
                                <input type="time" wire:model="manualCheckOut"
                                    class="w-full bg-surface-darker border border-white/10 rounded-lg text-white px-3 py-2">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-secondary mb-1">Notes</label>
                            <textarea wire:model="manualNotes" rows="2"
                                class="w-full bg-surface-darker border border-white/10 rounded-lg text-white px-3 py-2"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button wire:click="$set('showManualModal', false)"
                            class="px-4 py-2 border border-white/10 text-text-secondary rounded-lg hover:bg-white/5">
                            Annuler
                        </button>
                        <button wire:click="saveManualAttendance"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                            Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Validation Retard -->
    @if($showLateModal && $selectedLateArrival)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/60" wire:click="$set('showLateModal', false)"></div>
                <div class="relative bg-surface-dark border border-white/10 rounded-lg w-full max-w-md p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Traiter le retard</h3>
                    <div class="bg-amber-500/10 border border-amber-500/30 rounded-lg p-4 mb-4">
                        <p class="text-white font-medium">{{ $selectedLateArrival->employee->full_name }}</p>
                        <p class="text-text-secondary text-sm">
                            {{ $selectedLateArrival->date->format('d/m/Y') }} - Retard de {{ $selectedLateArrival->formatted_late_time }}
                        </p>
                        @if($selectedLateArrival->reason)
                            <p class="text-amber-400 text-sm mt-2">Motif: {{ $selectedLateArrival->reason }}</p>
                        @endif
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-text-secondary mb-2">Decision</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" wire:model="lateDecision" value="excused"
                                        class="text-primary focus:ring-primary">
                                    <span class="text-white">Excuser</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" wire:model="lateDecision" value="unexcused"
                                        class="text-red-500 focus:ring-red-500">
                                    <span class="text-white">Non excuse</span>
                                </label>
                            </div>
                        </div>
                        @if($lateDecision === 'unexcused')
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="deductFromSalary"
                                    class="rounded text-primary focus:ring-primary">
                                <span class="text-text-secondary text-sm">Deduire du salaire</span>
                            </label>
                        @endif
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button wire:click="$set('showLateModal', false)"
                            class="px-4 py-2 border border-white/10 text-text-secondary rounded-lg hover:bg-white/5">
                            Annuler
                        </button>
                        <button wire:click="validateLate"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90"
                            @if(!$lateDecision) disabled @endif>
                            Valider
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
