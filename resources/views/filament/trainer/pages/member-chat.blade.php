<x-filament::page>
    <div class="fi-chat-page">
        <div class="fi-chat-card">
            {{-- Header --}}
            <div class="fi-chat-header">
                <div class="fi-chat-user">
                    <div class="fi-chat-avatar">
                        {{ strtoupper(mb_substr($member->full_name ?? 'M', 0, 1)) }}
                    </div>

                    <div class="fi-chat-user-meta">
                        <h2>{{ $member->full_name }}</h2>
                        <p>Private conversation</p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="$refresh"
                    class="fi-chat-reload"
                >
                    Reload
                </button>
            </div>

            {{-- Messages --}}
            <div id="chat-box" class="fi-chat-messages">
                @forelse ($this->messages as $msg)
                    @php
                        $isMine = (int) $msg->sender_id === (int) auth()->user()->profile->id;
                    @endphp

                    <div class="fi-chat-row {{ $isMine ? 'mine' : 'theirs' }}">
                        @unless($isMine)
                            <div class="fi-chat-message-avatar">
                                {{ strtoupper(mb_substr($member->full_name ?? 'M', 0, 1)) }}
                            </div>
                        @endunless

                        <div class="fi-chat-message-wrap">
                            <div class="fi-chat-bubble {{ $isMine ? 'mine' : 'theirs' }}">
                                {{ $msg->content }}
                            </div>

                            <div class="fi-chat-time {{ $isMine ? 'mine' : 'theirs' }}">
                                {{ $msg->sent_at?->format('M j, Y g:i A') }}
                            </div>
                        </div>

                        @if($isMine)
                            <div class="fi-chat-message-avatar mine">
    {{ strtoupper(mb_substr(auth()->user()->profile->full_name ?? 'T', 0, 1)) }}
</div>
                        @endif
                    </div>
                @empty
                    <div class="fi-chat-empty">
                        <div class="fi-chat-empty-box">
                            <h3>No messages yet</h3>
                            <p>Start the conversation by sending the first message.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Composer --}}
            <div class="fi-chat-composer">
                <textarea
                    wire:model.defer="message"
                    wire:keydown.enter.prevent="send"
                    rows="2"
                    placeholder="Type a message..."
                    class="fi-chat-input"
                ></textarea>

                <button
                    wire:click="send"
                    type="button"
                    class="fi-chat-send"
                >
                    Send
                </button>
            </div>
        </div>
    </div>

    <style>
        .fi-chat-page {
            width: 100%;
        }

        .fi-chat-card {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 10rem);
            min-height: 650px;
            overflow: hidden;
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgb(17 24 39);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        .fi-chat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.02);
        }

        .fi-chat-user {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            min-width: 0;
        }

        .fi-chat-avatar {
            width: 3rem;
            height: 3rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(var(--primary-500), 0.15);
            color: rgb(var(--primary-400));
            font-weight: 700;
            font-size: 1rem;
            flex-shrink: 0;
            border: 1px solid rgba(var(--primary-500), 0.25);
        }

        .fi-chat-user-meta h2 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 700;
            color: white;
            line-height: 1.2;
        }

        .fi-chat-user-meta p {
            margin: 0.2rem 0 0;
            color: rgb(156 163 175);
            font-size: 0.95rem;
        }

        .fi-chat-reload {
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.04);
            color: white;
            border-radius: 0.875rem;
            padding: 0.75rem 1rem;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .fi-chat-reload:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        .fi-chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background:
                linear-gradient(to bottom, rgba(255,255,255,0.02), rgba(255,255,255,0.01)),
                rgb(15 23 42);
        }

        .fi-chat-row {
            display: flex;
            align-items: flex-end;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
            width: 100%;
        }

        .fi-chat-row.mine {
            justify-content: flex-end;
        }

        .fi-chat-row.theirs {
            justify-content: flex-start;
        }

        .fi-chat-message-wrap {
    display: flex;
    flex-direction: column;
    max-width: min(55%, 520px);
}

        .fi-chat-row.mine .fi-chat-message-wrap {
            align-items: flex-end;
        }

        .fi-chat-row.theirs .fi-chat-message-wrap {
            align-items: flex-start;
        }

        .fi-chat-message-avatar {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-weight: 700;
            font-size: 0.95rem;
            color: rgb(229 231 235);
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .fi-chat-message-avatar.mine {
            background: rgba(var(--primary-500), 0.14);
            color: rgb(var(--primary-400));
            border-color: rgba(var(--primary-500), 0.25);
        }

        .fi-chat-bubble {
            padding: 0.75rem 0.9rem;
            border-radius: 1.1rem;
            font-size: 0.98rem;
            line-height: 1.0;
            white-space: pre-wrap;
            word-break: break-word;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
        }

        .fi-chat-bubble.mine {
            background: rgb(var(--primary-600));
            color: white;
            border-bottom-right-radius: 0.4rem;
        }

        .fi-chat-bubble.theirs {
            background: rgba(255, 255, 255, 0.04);
            color: rgb(243 244 246);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-bottom-left-radius: 0.4rem;
        }

        .fi-chat-time {
            margin-top: 0.35rem;
            padding-inline: 0.25rem;
            font-size: 0.78rem;
            color: rgb(156 163 175);
        }

        .fi-chat-empty {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .fi-chat-empty-box {
            text-align: center;
            padding: 2rem;
            border-radius: 1rem;
            border: 1px dashed rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.02);
        }

        .fi-chat-empty-box h3 {
            margin: 0;
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .fi-chat-empty-box p {
            margin: 0.4rem 0 0;
            color: rgb(156 163 175);
        }

        .fi-chat-composer {
            display: flex;
            align-items: flex-end;
            gap: 0.875rem;
            padding: 1.25rem 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.02);
        }

        .fi-chat-input {
            flex: 1;
            min-height: 56px;
            max-height: 140px;
            resize: none;
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgb(3 7 18);
            color: white;
            padding: 1rem 1.1rem;
            font-size: 0.95rem;
            line-height: 1.5;
            outline: none;
            transition: 0.2s ease;
        }

        .fi-chat-input::placeholder {
            color: rgb(107 114 128);
        }

        .fi-chat-input:focus {
            border-color: rgba(var(--primary-500), 0.5);
            box-shadow: 0 0 0 4px rgba(var(--primary-500), 0.12);
        }

        .fi-chat-send {
            height: 56px;
            min-width: 110px;
            border: none;
            border-radius: 1rem;
            background: rgb(var(--primary-600));
            color: white;
            font-weight: 700;
            font-size: 0.95rem;
            padding: 0 1.5rem;
            transition: 0.2s ease;
        }

        .fi-chat-send:hover {
            background: rgb(var(--primary-500));
        }

        @media (max-width: 768px) {
            .fi-chat-card {
                height: calc(100vh - 8rem);
                min-height: 560px;
            }

            .fi-chat-header,
            .fi-chat-composer,
            .fi-chat-messages {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .fi-chat-message-wrap {
                max-width: 85%;
            }

            .fi-chat-send {
                min-width: 90px;
                padding: 0 1rem;
            }

            .fi-chat-user-meta h2 {
                font-size: 1.1rem;
            }
        }
    </style>

    <script>
        function scrollChatToBottom() {
            const chatBox = document.getElementById('chat-box');
            if (chatBox) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        }

        document.addEventListener('livewire:init', () => {
            setTimeout(scrollChatToBottom, 150);
        });

        document.addEventListener('message-sent', () => {
            setTimeout(scrollChatToBottom, 100);
        });
    </script>
</x-filament::page>
