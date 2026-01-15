<div class="space-y-6">
    <div class="flex justify-between items-center bg-surface-dark border border-[#3a2e24] p-6 rounded-2xl">
        <div>
            <h2 class="text-2xl font-bold text-white">Employés</h2>
            <p class="text-text-secondary text-sm">Gestion des ressources humaines.</p>
        </div>
        <div class="flex gap-2">
            <x-ui.button href="{{ route('hr.org-chart') }}" type="secondary" class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">account_tree</span>
                Organigramme
            </x-ui.button>
            <x-ui.button href="{{ route('hr.employees.create') }}" type="primary"
                class="flex items-center gap-2 shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-[20px]">add_circle</span>
                Nouvel Employé
            </x-ui.button>
        </div>
    </div>

    <!-- Filters -->
    <x-ui.card class="bg-surface-dark border border-[#3a2e24]">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-ui.input wire:model.live.debounce.300ms="search" label="Recherche"
                placeholder="Nom, Email, Matricule..." />

            <x-ui.select wire:model.live="departmentId" label="Département">
                <option value="">Tous les départements</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select wire:model.live="status" label="Statut">
                <option value="">Tous les statuts</option>
                <option value="active">Actif</option>
                <option value="inactive">Inactif</option>
                <option value="terminated">Terminé</option>
            </x-ui.select>

            <x-ui.select wire:model.live="contractType" label="Type de Contrat">
                <option value="">Tous les contrats</option>
                <option value="cdi">CDI</option>
                <option value="cdd">CDD</option>
                <option value="interim">Intérim</option>
                <option value="stage">Stage</option>
                <option value="alternance">Alternance</option>
            </x-ui.select>
        </div>
    </x-ui.card>

    <!-- Table -->
    <div class="bg-surface-dark border border-[#3a2e24] rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#3a2e24]">
                <thead class="bg-surface-highlight">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Matricule</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Employé</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Poste & Département</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Manager</th>
                        <th
                            class="px-6 py-3 text-center text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Statut</th>
                        @can('view_salary', App\Modules\HR\Models\Employee::class)
                            <th class="px-6 py-3 text-right text-xs font-bold text-text-secondary uppercase tracking-wider">
                                Salaire</th>
                        @endcan
                        <th class="px-6 py-3 text-right text-xs font-bold text-text-secondary uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#3a2e24]">
                    @forelse($employees as $employee)
                        <tr class="hover:bg-surface-highlight/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-primary font-bold">
                                {{ $employee->employee_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center">
                                    <div
                                        class="size-8 rounded-full bg-surface-highlight flex items-center justify-center text-xs font-bold text-white mr-3 border border-[#3a2e24]">
                                        {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-white">{{ $employee->full_name }}</div>
                                        <div class="text-xs text-text-secondary">{{ $employee->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">
                                <div class="text-white">{{ $employee->job_title }}</div>
                                <div class="text-xs opacity-70">{{ $employee->department->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">
                                {{ $employee->manager ? $employee->manager->full_name : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $statusColors = [
                                        'active' => 'green',
                                        'inactive' => 'gray',
                                        'terminated' => 'red',
                                    ];
                                @endphp
                                <x-ui.badge :color="$statusColors[$employee->status] ?? 'gray'">
                                    {{ ucfirst($employee->status) }}
                                </x-ui.badge>
                            </td>
                            @can('view_salary', App\Modules\HR\Models\Employee::class)
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-white">
                                    {{ $employee->salary ? number_format($employee->salary, 2, ',', ' ') . ' FCFA' : '-' }}
                                </td>
                            @endcan
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('hr.employees.show', $employee) }}"
                                        class="p-2 rounded-lg text-text-secondary hover:text-primary hover:bg-primary/10 transition-colors"
                                        title="Voir">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </a>
                                    <a href="{{ route('hr.employees.edit', $employee) }}"
                                        class="p-2 rounded-lg text-text-secondary hover:text-yellow-500 hover:bg-yellow-500/10 transition-colors"
                                        title="Modifier">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </a>
                                    <button wire:click="confirmDelete({{ $employee->id }})"
                                        class="p-2 rounded-lg text-text-secondary hover:text-red-500 hover:bg-red-500/10 transition-colors"
                                        title="Supprimer">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-text-secondary">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">group_off</span>
                                    <p>Aucun employé trouvé.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-[#3a2e24]">
            {{ $employees->links() }}
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    @if($showDeleteModal && $employeeToDelete)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/60 transition-opacity" wire:click="cancelDelete"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-surface-dark rounded-2xl border border-[#3a2e24] text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-red-500 text-2xl">warning</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-white" id="modal-title">
                                Confirmer la suppression
                            </h3>
                            <p class="mt-2 text-sm text-text-secondary">
                                Etes-vous sur de vouloir supprimer l'employe <span class="font-bold text-white">{{ $employeeToDelete->full_name }}</span> ?
                                Cette action est irreversible.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-surface-highlight border-t border-[#3a2e24] flex justify-end gap-3">
                    <button wire:click="cancelDelete" type="button"
                        class="px-4 py-2 rounded-lg border border-[#3a2e24] text-white hover:bg-surface-dark transition-colors">
                        Annuler
                    </button>
                    <button wire:click="deleteEmployee" type="button"
                        class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>