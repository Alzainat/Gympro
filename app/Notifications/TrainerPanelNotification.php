<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TrainerPanelNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public string $type = 'info',
        public ?string $actionUrl = null,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    // ✅ مهم: Laravel يستخدم toArray للـ database channel إذا ما لقى toDatabase
    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'actions' => $this->actionUrl ? [
            [
                'name' => 'open',
                'label' => 'Open',
                'url' => $this->actionUrl,
            ],
        ] : [],
            'type' => $this->type,
            'action_url' => $this->actionUrl,
        ];
    }
}