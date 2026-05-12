<?php

namespace App\Notifications\User;

use App\Models\Deposit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DepositCreatedNotification extends Notification
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
            'type' => 'deposit_created',
            'title' => 'Deposit Submitted',
            'message' => 'Your deposit of ₹' . number_format($this->deposit->amount, 2) . ' has been submitted and is being processed.',
            'icon' => 'arrow-down-circle',
            'color' => 'blue',
            'amount' => $this->deposit->amount,
            'deposit_id' => $this->deposit->id,
        ];
    }
}
