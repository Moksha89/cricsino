<?php

namespace App\Notifications\User;

use App\Models\Deposit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DepositFailedNotification extends Notification
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
            'type' => 'deposit_failed',
            'title' => 'Deposit Failed',
            'message' => 'Your deposit of ₹' . number_format($this->deposit->amount, 2) . ' could not be processed.',
            'icon' => 'x-circle',
            'color' => 'red',
            'amount' => $this->deposit->amount,
            'deposit_id' => $this->deposit->id,
        ];
    }
}
