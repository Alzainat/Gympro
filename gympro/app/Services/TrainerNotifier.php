<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class TrainerNotifier
{
    public static function notifyTrainerOfMember(
        Profile $member,
        string $title,
        string $body,
        string $type = 'info',
        ?string $actionUrl = null
    ): void {
        if (! $member->trainer_id) return;

        $trainerProfile = Profile::find($member->trainer_id);
        if (! $trainerProfile) return;

        $trainerUser = User::find($trainerProfile->user_id);
        if (! $trainerUser) return;

        self::send($trainerUser, $title, $body, $type, $actionUrl);
    }

    public static function notifyTrainerProfile(
        Profile $trainerProfile,
        string $title,
        string $body,
        string $type = 'info',
        ?string $actionUrl = null
    ): void {
        $trainerUser = User::find($trainerProfile->user_id);
        if (! $trainerUser) return;

        self::send($trainerUser, $title, $body, $type, $actionUrl);
    }

    private static function send(
        User $trainerUser,
        string $title,
        string $body,
        string $type = 'info',
        ?string $actionUrl = null
    ): void {
        $n = Notification::make()
            ->title($title)
            ->body($body);

        $type = strtolower($type);
        if ($type === 'alert') {
            $n->danger();
        } elseif ($type === 'reminder') {
            $n->warning();
        } else {
            $n->info();
        }

        if ($actionUrl) {
            $n->actions([
                Action::make('open')
                    ->label('Open')
                    ->url($actionUrl),
            ]);
        }

        // ✅ هذه تكتب Database Notification بصيغة Filament اللي الجرس بعرضها
        $n->sendToDatabase($trainerUser);
    }
}