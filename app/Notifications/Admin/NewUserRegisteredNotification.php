<?php

namespace App\Notifications\Admin;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewUserRegisteredNotification extends Notification
{
    use Queueable;

    public function __construct(public User $registeredUser)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'admin_new_user',
            'title' => 'New User Registered',
            'message' => $this->registeredUser->name . ' (' . $this->registeredUser->email . ') has registered.',
            'icon' => 'user-plus',
            'color' => 'green',
            'user_id' => $this->registeredUser->id,
            'user_name' => $this->registeredUser->name,
        ];
    }
}
