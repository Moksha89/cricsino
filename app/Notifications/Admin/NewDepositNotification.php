<?php

namespace App\Notifications\Admin;

use App\Models\Deposit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewDepositNotification extends Notification
{
    use Queueable;

    public function __construct(public Deposit $deposit)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'admin_new_deposit',
            'title' => 'New Deposit Request',
            'message' => $this->deposit->user->name . ' submitted a deposit of ₹' . number_format($this->deposit->amount, 2) . '.',
            'icon' => 'arrow-down-circle',
            'color' => 'blue',
            'amount' => $this->deposit->amount,
            'user_name' => $this->deposit->user->name,
            'deposit_id' => $this->deposit->id,
        ];
    }
}
