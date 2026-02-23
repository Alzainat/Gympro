<x-filament::page>
    <div class="rounded-2xl p-6 space-y-6 bg-gray-900 border border-gray-700 shadow-xl">

        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-700 pb-4">
            <div>
                <h2 class="text-xl font-bold text-white">
                    Chat with {{ $member->full_name }}
                </h2>
                <p class="text-sm text-gray-400">
                    Private conversation
                </p>
            </div>
        </div>

        <!-- Messages -->
        <div class="h-[500px] overflow-y-auto space-y-4 pr-2">

            @foreach ($this->messages as $msg)
                @php
                    $isMine = $msg->sender_id === auth()->user()->profile->id;
                @endphp

                <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[70%] px-4 py-3 rounded-2xl text-sm shadow-md
                        {{ $isMine
                            ? 'bg-green-600 text-black'
                            : 'bg-gray-800 text-gray-100 border border-gray-700'
                        }}">
                        <div class="whitespace-pre-wrap leading-relaxed">
                            {{ $msg->content }}
                        </div>

                        <div class="text-[11px] mt-2 opacity-70 text-right">
                            {{ $msg->sent_at->format('Y-m-d H:i') }}
                        </div>
                    </div>
                </div>
            @endforeach

        </div>

        <!-- Composer -->
        <div class="flex items-center gap-3 pt-4 border-t border-gray-700">
            <input
    type="text"
    wire:model.defer="message"
    wire:keydown.enter="send"
    placeholder="Type your message..."
    class="flex-1 rounded-xl border border-gray-300 px-4 py-3
           !bg-white !text-black placeholder:!text-gray-500
           !caret-black focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
    style="-webkit-text-fill-color: #000 !important; color:#000 !important; caret-color:#000 !important;"
/>

            <button
                wire:click="send"
                class="px-6 py-3 rounded-xl bg-green-600 hover:bg-green-700 text-black font-semibold transition shadow-lg"
            >
                Send
            </button>
        </div>
    </div>
</x-filament::page>
