<?php

namespace App\Notifications\Admin;

use App\Models\Agent;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AgentCreditDebitNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Agent $agent,
        public string $action,
        public float $amount
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $actionLabel = $this->action === 'credit' ? 'credited' : 'debited';
        return [
            'type' => 'admin_agent_' . $this->action,
            'title' => 'Agent ' . ucfirst($this->action),
            'message' => 'Agent ' . $this->agent->user->name . ' was ' . $actionLabel . ' ₹' . number_format($this->amount, 2) . '.',
            'icon' => $this->action === 'credit' ? 'plus-circle' : 'minus-circle',
            'color' => $this->action === 'credit' ? 'green' : 'amber',
            'amount' => $this->amount,
            'agent_id' => $this->agent->id,
            'action' => $this->action,
        ];
    }
}
