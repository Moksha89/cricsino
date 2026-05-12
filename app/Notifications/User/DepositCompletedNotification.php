<?php

namespace App\Notifications\User;

use App\Models\Deposit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DepositCompletedNotification extends Notification
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
            'type' => 'deposit_completed',
            'title' => 'Deposit Approved',
            'message' => 'Your deposit of ₹' . number_format($this->deposit->amount, 2) . ' has been approved and credited to your account.',
            'icon' => 'check-circle',
            'color' => 'green',
            'amount' => $this->deposit->amount,
            'deposit_id' => $this->deposit->id,
        ];
    }
}
