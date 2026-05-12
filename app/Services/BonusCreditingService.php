<?php

namespace App\Services;

use App\Models\BonusTransaction;
use App\Models\Promotion;
use App\Models\PromotionClaim;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BonusCreditingService
{
    /**
     * Credit bonus to user's bonus balance within a DB transaction.
     * Uses row-level locking on the user row for safety.
     */
    public static function creditBonus(PromotionClaim $claim, ?int $adminId = null): bool
    {
        return DB::transaction(function () use ($claim, $adminId) {
            $user = User::lockForUpdate()->find($claim->user_id);
            if (!$user) {
                return false;
            }

            $balanceBefore = (float) ($user->bonus ?? 0);
            $bonusAmount = (float) $claim->bonus_amount;
            $balanceAfter = round($balanceBefore + $bonusAmount, 2);

            $user->bonus = $balanceAfter;
            $user->save();

            BonusTransaction::create([
                'user_id' => $user->id,
                'promotion_id' => $claim->promotion_id,
                'claim_id' => $claim->id,
                'amount' => $bonusAmount,
                'type' => 'credit',
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => 'Bonus credited: ' . ($claim->promotion->title ?? 'Promotion #' . $claim->promotion_id),
                'created_by' => $adminId,
            ]);

            $claim->update([
                'status' => 'credited',
                'credited_at' => now(),
                'approved_by' => $adminId ?? $claim->approved_by,
                'approved_at' => $claim->approved_at ?? now(),
            ]);

            return true;
        });
    }

    /**
     * Create a pending claim for a user on a promotion.
     * Performs all eligibility checks server-side.
     */
    public static function createClaim(Promotion $promotion, User $user, float $depositAmount = 0, ?int $depositId = null): PromotionClaim|string
    {
        if (!$promotion->isActive()) {
            return 'This promotion is not currently active.';
        }

        if (!$promotion->canUserClaim($user)) {
            return 'You are not eligible for this promotion or have reached the claim limit.';
        }

        if ($promotion->min_deposit && $depositAmount < (float) $promotion->min_deposit) {
            return 'Minimum deposit of ₹' . number_format((float) $promotion->min_deposit, 2) . ' required.';
        }

        $bonusAmount = $promotion->calculateBonus($depositAmount);
        if ($bonusAmount <= 0) {
            return 'No bonus amount calculated for this promotion.';
        }

        return DB::transaction(function () use ($promotion, $user, $depositAmount, $depositId, $bonusAmount) {
            // Re-check claim count inside transaction for concurrency safety
            $existingClaims = PromotionClaim::where('promotion_id', $promotion->id)
                ->where('user_id', $user->id)
                ->whereNotIn('status', ['rejected', 'cancelled'])
                ->lockForUpdate()
                ->count();

            if ($promotion->claim_limit_per_user !== null && $existingClaims >= $promotion->claim_limit_per_user) {
                return 'You have already claimed this promotion.';
            }

            if ($promotion->total_claim_limit !== null) {
                $totalClaims = PromotionClaim::where('promotion_id', $promotion->id)
                    ->whereNotIn('status', ['rejected', 'cancelled'])
                    ->lockForUpdate()
                    ->count();
                if ($totalClaims >= $promotion->total_claim_limit) {
                    return 'This promotion has reached its maximum claims.';
                }
            }

            $wageringRequired = null;
            if ($promotion->wagering_multiplier) {
                $wageringRequired = round($bonusAmount * (float) $promotion->wagering_multiplier, 2);
            }

            $status = in_array($promotion->type, ['welcome', 'manual']) ? 'pending' : 'pending';

            return PromotionClaim::create([
                'promotion_id' => $promotion->id,
                'user_id' => $user->id,
                'deposit_id' => $depositId,
                'amount' => $depositAmount,
                'bonus_amount' => $bonusAmount,
                'status' => $status,
                'wagering_required' => $wageringRequired,
                'wagering_completed' => 0,
            ]);
        });
    }

    /**
     * Admin approves a pending claim (does not credit yet).
     */
    public static function approveClaim(PromotionClaim $claim, int $adminId): bool
    {
        if ($claim->status !== 'pending') {
            return false;
        }

        $claim->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        return true;
    }

    /**
     * Admin rejects a pending claim.
     */
    public static function rejectClaim(PromotionClaim $claim, int $adminId): bool
    {
        if (!in_array($claim->status, ['pending', 'approved'])) {
            return false;
        }

        $claim->update([
            'status' => 'rejected',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        return true;
    }

    /**
     * Admin cancels a claim.
     */
    public static function cancelClaim(PromotionClaim $claim): bool
    {
        if (in_array($claim->status, ['credited', 'cancelled'])) {
            return false;
        }

        $claim->update(['status' => 'cancelled']);

        return true;
    }
}
