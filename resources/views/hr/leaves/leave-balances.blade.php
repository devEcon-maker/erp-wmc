<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    @if(!$hasEmployeeProfile)
        <div class="col-span-full">
            <x-ui.alert type="warning">
                Vous n'avez pas de profil employé associé. Veuillez contacter les RH.
            </x-ui.alert>
        </div>
    @else
        @foreach($balances as $balance)
            @php
                $percentage = $balance->allocated > 0 ? ($balance->used / $balance->allocated) * 100 : 0;
                $color = match ($balance->leaveType->color) {
                    'blue' => 'text-blue-500',
                    'purple' => 'text-purple-500',
                    'red' => 'text-red-500',
                    'green' => 'text-green-500',
                    default => 'text-gray-500'
                };
            @endphp
            <div class="bg-surface-dark border border-white/10 rounded-xl p-6 shadow-lg">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-text-secondary text-sm font-medium">{{ $balance->leaveType->name }}</h3>
                        <div class="mt-2 flex items-baseline">
                            <span class="text-2xl font-bold text-white">{{ $balance->remaining + 0 }}</span>
                            <span class="ml-1 text-sm text-text-secondary">jours restants</span>
                        </div>
                    </div>
                    <div class="relative w-12 h-12">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-gray-700"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none"
                                stroke="currentColor" stroke-width="3" />
                            <path class="{{ $color }}" stroke-dasharray="{{ $percentage }}, 100"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none"
                                stroke="currentColor" stroke-width="3" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 text-xs text-text-secondary">
                    <div class="flex justify-between mb-1">
                        <span>Alloués</span>
                        <span class="text-white">{{ $balance->allocated + 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Utilisés</span>
                        <span class="text-white">{{ $balance->used + 0 }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>