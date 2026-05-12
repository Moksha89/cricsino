<?php

namespace App\Notifications\Admin;

use App\Models\SupportConversation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewSupportTicketNotification extends Notification
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
            'type' => 'admin_new_support_ticket',
            'title' => 'New Support Ticket',
            'message' => $this->conversation->user->name . ' opened a support ticket: ' . str($this->conversation->subject)->limit(50),
            'icon' => 'message-square',
            'color' => 'blue',
            'conversation_id' => $this->conversation->id,
            'category' => $this->conversation->category,
        ];
    }
}
