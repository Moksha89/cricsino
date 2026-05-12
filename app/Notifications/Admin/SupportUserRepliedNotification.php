<?php

namespace App\Notifications\Admin;

use App\Models\SupportConversation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SupportUserRepliedNotification extends Notification
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
            'type' => 'admin_support_user_replied',
            'title' => 'Support Reply',
            'message' => $this->conversation->user->name . ' replied to ticket #' . $this->conversation->id . '.',
            'icon' => 'message-square',
            'color' => 'amber',
            'conversation_id' => $this->conversation->id,
        ];
    }
}
