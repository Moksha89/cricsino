<?php

namespace App\Notifications\Admin;

use App\Models\Withdraw;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewWithdrawalNotification extends Notification
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
            'type' => 'admin_new_withdrawal',
            'title' => 'New Withdrawal Request',
            'message' => $this->withdraw->user->name . ' requested a withdrawal of ₹' . number_format($this->withdraw->amount, 2) . '.',
            'icon' => 'arrow-up-circle',
            'color' => 'amber',
            'amount' => $this->withdraw->amount,
            'user_name' => $this->withdraw->user->name,
            'withdraw_id' => $this->withdraw->id,
        ];
    }
}
