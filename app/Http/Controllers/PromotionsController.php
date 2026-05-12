<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\PromotionClaim;
use App\Services\BonusCreditingService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PromotionsController extends Controller
{
    public function index(Request $request)
    {
        $promotions = Promotion::query()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', now());
            })
            ->latest()
            ->get();

        $userClaims = PromotionClaim::where('user_id', $request->user()->id)
            ->with('promotion:id,title,type,code')
            ->latest()
            ->get();

        // Determine which promotions the user can still claim
        $promotions->each(function ($promo) use ($request) {
            $promo->can_claim = $promo->canUserClaim($request->user());
            $promo->user_claims_count = $promo->claims()
                ->where('user_id', $request->user()->id)
                ->whereNotIn('status', ['rejected', 'cancelled'])
                ->count();
        });

        return Inertia::render('Account/Promotions', [
            'promotions' => $promotions,
            'claims' => $userClaims,
            'bonusBalance' => (float) ($request->user()->bonus ?? 0),
        ]);
    }

    public function claim(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'deposit_amount' => 'nullable|numeric|min:0',
        ]);

        $depositAmount = (float) ($validated['deposit_amount'] ?? 0);

        $result = BonusCreditingService::createClaim(
            $promotion,
            $request->user(),
            $depositAmount
        );

        if (is_string($result)) {
            return back()->with('error', $result);
        }

        NotificationService::promotionClaimed($result);

        return back()->with('success', 'Promotion claimed successfully. Pending admin approval.');
    }

    public function claimHistory(Request $request)
    {
        $claims = PromotionClaim::where('user_id', $request->user()->id)
            ->with('promotion:id,title,type,code')
            ->latest()
            ->paginate(20);

        return Inertia::render('Account/PromotionClaims', [
            'claims' => $claims,
            'bonusBalance' => (float) ($request->user()->bonus ?? 0),
        ]);
    }
}
