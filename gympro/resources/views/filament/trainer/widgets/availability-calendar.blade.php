<x-filament::card>
    <h2 class="text-lg font-bold mb-4">Weekly Availability</h2>

    <div class="grid grid-cols-7 gap-4">

        @foreach ([
            'Monday','Tuesday','Wednesday',
            'Thursday','Friday','Saturday','Sunday'
        ] as $day)

            @php
                $slots = $availability->where('day_of_week', $day);
            @endphp

            <div class="rounded-xl border p-3 bg-gray-900">
                <div class="font-semibold mb-2">{{ $day }}</div>

                @forelse ($slots as $slot)
                    <div class="text-sm text-green-400">
                        🟢 {{ $slot->start_time }} – {{ $slot->end_time }}
                    </div>
                @empty
                    <div class="text-xs text-gray-500">
                        Not available
                    </div>
                @endforelse
            </div>

        @endforeach

    </div>
</x-filament::card>