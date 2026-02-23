<?php

namespace App\Filament\Trainer\Pages;

use App\Models\Message;
use App\Models\Profile;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class MemberChat extends Page
{
    protected static ?string $slug = 'member-chat/{record}';
    protected static string $view = 'filament.trainer.pages.member-chat';
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Member Chat';

    public Profile $member;
    public string $message = '';

    protected int $perPage = 50;

    public function mount(Profile $record): void
    {
        $user = auth()->user();

        abort_unless($user && $user->profile, 403);

        $trainerProfile = $user->profile;

        // ✅ تأكد إنه Trainer فعلاً
        abort_unless($trainerProfile->role === 'trainer', 403);

        // ✅ تأكد إن العضو تابع له
        abort_unless(
            $record->role === 'member' &&
            (int) $record->trainer_id === (int) $trainerProfile->id,
            403
        );

        $this->member = $record;

        // ✅ Mark messages as read لما يفتح الشات
        Message::query()
            ->where('sender_id', $this->member->id)
            ->where('receiver_id', $trainerProfile->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Get chat messages (latest first with limit)
     */
    public function getMessagesProperty(): Collection
    {
        $trainerId = auth()->user()->profile->id;

        return Message::query()
            ->where(function (Builder $q) use ($trainerId) {
                $q->where('sender_id', $trainerId)
                  ->where('receiver_id', $this->member->id);
            })
            ->orWhere(function (Builder $q) use ($trainerId) {
                $q->where('sender_id', $this->member->id)
                  ->where('receiver_id', $trainerId);
            })
            ->orderByDesc('sent_at')
            ->limit($this->perPage)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * Send message
     */
    public function send(): void
    {
        $content = trim($this->message);

        if ($content === '') {
            return;
        }

        $trainerId = auth()->user()->profile->id;

        Message::create([
            'sender_id'   => $trainerId,
            'receiver_id' => $this->member->id,
            'content'     => $content,
            'sent_at'     => now(),
            'is_read'     => false,
        ]);

        $this->reset('message');

        // 🔄 Refresh messages after sending
        $this->dispatch('$refresh');
    }
}
