@props(['headers'])

<div class="flex flex-col">
    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-[#3a2e24]">
                    <thead class="bg-surface-highlight/50">
                        <tr>
                            @foreach ($headers as $header)
                                <th scope="col"
                                    class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    {{ $header }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#3a2e24]">
                        {{ $slot }}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>