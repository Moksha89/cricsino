<?php

namespace App\Notifications\User;

use App\Models\SupportConversation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SupportAdminRepliedNotification extends Notification
{
    use Queueable;

    public function __construct(protected SupportConversation $conversation)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'support_admin_replied',
            'title' => 'Support Reply',
            'message' => 'An admin has replied to your support ticket: ' . str($this->conversation->subject)->limit(50),
            'icon' => 'message-square',
            'color' => 'green',
            'conversation_id' => $this->conversation->id,
        ];
    }
}
