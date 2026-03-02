<x-filament::card>
    <h2 class="text-lg font-bold mb-4">Weekly Availability</h2>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 gap-4">

        @foreach ([
            'Monday','Tuesday','Wednesday',
            'Thursday','Friday','Saturday','Sunday'
        ] as $day)

            @php
                $slots = $availability->where('day_of_week', $day);
            @endphp

            <div class="rounded-xl border border-gray-700 p-3 bg-gray-900/50 min-h-[92px]">
                <div class="font-semibold mb-2 text-sm text-center whitespace-nowrap">
                    {{ $day }}
                </div>

                <div class="space-y-1">
                    @forelse ($slots as $slot)
                        <div class="text-xs text-green-400 flex items-center justify-center gap-1">
                            <span>🟢</span>
                            <span class="whitespace-nowrap">{{ $slot->start_time }} – {{ $slot->end_time }}</span>
                        </div>
                    @empty
                        <div class="text-xs text-gray-500 text-center">
                            Not available
                        </div>
                    @endforelse
                </div>
            </div>

        @endforeach

    </div>
</x-filament::card>
