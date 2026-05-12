<?php

namespace App\Notifications\Admin;

use App\Models\PromotionClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewPromotionClaimNotification extends Notification
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
            'type' => 'new_promotion_claim',
            'title' => 'New Promotion Claim',
            'message' => ($this->claim->user->name ?? 'User') . ' claimed "' . ($this->claim->promotion->title ?? 'Promotion') . '" — ₹' . number_format((float) $this->claim->bonus_amount, 2),
            'icon' => 'gift',
            'color' => 'purple',
            'claim_id' => $this->claim->id,
            'promotion_id' => $this->claim->promotion_id,
        ];
    }
}
