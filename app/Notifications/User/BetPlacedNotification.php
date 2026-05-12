<?php

namespace App\Notifications\User;

use App\Models\Stake;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BetPlacedNotification extends Notification
{
    use Queueable;

    public function __construct(public Stake $stake)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $gameName = $this->stake->game?->name ?? 'Unknown';
        return [
            'type' => 'bet_placed',
            'title' => 'Bet Placed',
            'message' => 'Your bet of ₹' . number_format($this->stake->amount, 2) . ' on ' . $gameName . ' has been placed.',
            'icon' => 'target',
            'color' => 'blue',
            'amount' => $this->stake->amount,
            'stake_id' => $this->stake->id,
        ];
    }
}
