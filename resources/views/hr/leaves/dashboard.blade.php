<div class="space-y-8">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-white">Gestion des Congés</h1>
        <p class="mt-1 text-sm text-text-secondary">Consultez vos soldes, faites des demandes et gérez les approbations.
        </p>
    </div>

    <!-- Balances Section -->
    <livewire:hr.leaves.leave-balances />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- My Requests -->
        <div>
            <livewire:hr.leaves.leave-requests-list />
        </div>

        <!-- Manager Approvals (Only if manager) -->
        @can('leaves.approve')
            <div>
                <livewire:hr.leaves.leave-approval />
            </div>
        @endcan
    </div>
</div>