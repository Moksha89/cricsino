<?php

namespace App\Listeners;

use App\Services\NotificationService;
use Illuminate\Auth\Events\Registered;

class NotifyAdminOfNewUser
{
    public function handle(Registered $event): void
    {
        NotificationService::userRegistered($event->user);
    }
}
