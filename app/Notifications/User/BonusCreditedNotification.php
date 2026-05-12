<?php

namespace App\Notifications\User;

use App\Models\PromotionClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BonusCreditedNotification extends Notification
{
    use Queueable;

    public function __construct(public PromotionClaim $claim)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'bonus_credited',
            'title' => 'Bonus Credited',
            'message' => 'Your bonus of ₹' . number_format((float) $this->claim->bonus_amount, 2) . ' has been credited to your account.',
            'icon' => 'gift',
            'color' => 'green',
            'amount' => $this->claim->bonus_amount,
            'promotion_title' => $this->claim->promotion->title ?? 'Promotion',
        ];
    }
}
