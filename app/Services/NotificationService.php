<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\User;
use App\Models\Withdraw;
use App\Models\Stake;
use App\Models\Agent;
use App\Models\SupportConversation;
use App\Notifications\Admin\AgentCreditDebitNotification;
use App\Notifications\Admin\NewDepositNotification;
use App\Notifications\Admin\NewSupportTicketNotification;
use App\Notifications\Admin\NewUserRegisteredNotification;
use App\Notifications\Admin\NewWithdrawalNotification;
use App\Notifications\Admin\SupportUserRepliedNotification;
use App\Notifications\User\BetPlacedNotification;
use App\Notifications\User\DepositCompletedNotification;
use App\Notifications\User\DepositCreatedNotification;
use App\Notifications\User\DepositFailedNotification;
use App\Notifications\User\SupportAdminRepliedNotification;
use App\Notifications\User\WithdrawalApprovedNotification;
use App\Notifications\User\WithdrawalRejectedNotification;
use App\Notifications\User\WithdrawalRequestedNotification;

class NotificationService
{
    public static function depositCreated(Deposit $deposit): void
    {
        $deposit->user->notify(new DepositCreatedNotification($deposit));
        static::notifyAdmins(new NewDepositNotification($deposit));
    }

    public static function depositCompleted(Deposit $deposit): void
    {
        $deposit->user->notify(new DepositCompletedNotification($deposit));
    }

    public static function depositFailed(Deposit $deposit): void
    {
        $deposit->user->notify(new DepositFailedNotification($deposit));
    }

    public static function withdrawalRequested(Withdraw $withdraw): void
    {
        $withdraw->user->notify(new WithdrawalRequestedNotification($withdraw));
        static::notifyAdmins(new NewWithdrawalNotification($withdraw));
    }

    public static function withdrawalApproved(Withdraw $withdraw): void
    {
        $withdraw->user->notify(new WithdrawalApprovedNotification($withdraw));
    }

    public static function withdrawalRejected(Withdraw $withdraw): void
    {
        $withdraw->user->notify(new WithdrawalRejectedNotification($withdraw));
    }

    public static function betPlaced(Stake $stake): void
    {
        $stake->user->notify(new BetPlacedNotification($stake));
    }

    public static function userRegistered(User $user): void
    {
        static::notifyAdmins(new NewUserRegisteredNotification($user));
    }

    public static function agentCreditDebit(Agent $agent, string $action, float $amount): void
    {
        static::notifyAdmins(new AgentCreditDebitNotification($agent, $action, $amount));
    }

    public static function supportTicketCreated(SupportConversation $conversation): void
    {
        static::notifyAdmins(new NewSupportTicketNotification($conversation));
    }

    public static function supportUserReplied(SupportConversation $conversation): void
    {
        static::notifyAdmins(new SupportUserRepliedNotification($conversation));
    }

    public static function supportAdminReplied(SupportConversation $conversation): void
    {
        $conversation->user->notify(new SupportAdminRepliedNotification($conversation));
    }

    protected static function notifyAdmins($notification): void
    {
        $admins = User::where('is_admin', true)->get();
        foreach ($admins as $admin) {
            $admin->notify($notification);
        }
    }
}
