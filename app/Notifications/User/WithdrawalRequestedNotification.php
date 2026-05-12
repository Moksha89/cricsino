<?php

namespace App\Notifications\User;

use App\Models\Withdraw;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WithdrawalRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(public Withdraw $withdraw)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'withdrawal_requested',
            'title' => 'Withdrawal Requested',
            'message' => 'Your withdrawal of ₹' . number_format($this->withdraw->amount, 2) . ' has been submitted for processing.',
            'icon' => 'arrow-up-circle',
            'color' => 'blue',
            'amount' => $this->withdraw->amount,
            'withdraw_id' => $this->withdraw->id,
        ];
    }
}
