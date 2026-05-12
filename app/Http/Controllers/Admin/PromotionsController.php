<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\PromotionClaim;
use App\Services\BonusCreditingService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PromotionsController extends Controller
{
    public function index(Request $request)
    {
        $promotions = Promotion::query()
            ->withCount('claims')
            ->withSum(['claims as total_bonus_credited' => fn ($q) => $q->where('status', 'credited')], 'bonus_amount')
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"))
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Promotions/Index', [
            'promotions' => $promotions,
            'filters' => $request->only(['type', 'status', 'search']),
            'types' => Promotion::TYPES,
            'statuses' => Promotion::STATUSES,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Promotions/Create', [
            'types' => Promotion::TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'nullable|string|max:100|unique:promotions,code',
            'description' => 'nullable|string|max:2000',
            'type' => 'required|string|in:' . implode(',', Promotion::TYPES),
            'status' => 'required|string|in:' . implode(',', Promotion::STATUSES),
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'min_deposit' => 'nullable|numeric|min:0',
            'max_bonus' => 'nullable|numeric|min:0',
            'bonus_percent' => 'nullable|numeric|min:0|max:100',
            'fixed_bonus_amount' => 'nullable|numeric|min:0',
            'wagering_multiplier' => 'nullable|numeric|min:0',
            'eligible_user_level' => 'nullable|string',
            'claim_limit_per_user' => 'nullable|integer|min:1',
            'total_claim_limit' => 'nullable|integer|min:1',
            'terms_text' => 'nullable|string|max:5000',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = Str::slug($validated['title']) . '-' . Str::random(6);
        }

        $validated['created_by'] = $request->user()->id;

        Promotion::create($validated);

        return redirect()->route('admin.promotions.index')->with('success', 'Promotion created.');
    }

    public function show(Promotion $promotion)
    {
        $promotion->loadCount([
            'claims',
            'claims as pending_claims_count' => fn ($q) => $q->where('status', 'pending'),
            'claims as credited_claims_count' => fn ($q) => $q->where('status', 'credited'),
            'claims as rejected_claims_count' => fn ($q) => $q->where('status', 'rejected'),
        ]);
        $promotion->loadSum(['claims as total_bonus_credited' => fn ($q) => $q->where('status', 'credited')], 'bonus_amount');

        $claims = PromotionClaim::where('promotion_id', $promotion->id)
            ->with(['user:id,name,email', 'approver:id,name'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Promotions/Show', [
            'promotion' => $promotion,
            'claims' => $claims,
            'claimStatuses' => PromotionClaim::STATUSES,
        ]);
    }

    public function edit(Promotion $promotion)
    {
        return Inertia::render('Admin/Promotions/Edit', [
            'promotion' => $promotion,
            'types' => Promotion::TYPES,
        ]);
    }

    public function update(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'nullable|string|max:100|unique:promotions,code,' . $promotion->id,
            'description' => 'nullable|string|max:2000',
            'type' => 'required|string|in:' . implode(',', Promotion::TYPES),
            'status' => 'required|string|in:' . implode(',', Promotion::STATUSES),
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'min_deposit' => 'nullable|numeric|min:0',
            'max_bonus' => 'nullable|numeric|min:0',
            'bonus_percent' => 'nullable|numeric|min:0|max:100',
            'fixed_bonus_amount' => 'nullable|numeric|min:0',
            'wagering_multiplier' => 'nullable|numeric|min:0',
            'eligible_user_level' => 'nullable|string',
            'claim_limit_per_user' => 'nullable|integer|min:1',
            'total_claim_limit' => 'nullable|integer|min:1',
            'terms_text' => 'nullable|string|max:5000',
        ]);

        $promotion->update($validated);

        return redirect()->route('admin.promotions.show', $promotion)->with('success', 'Promotion updated.');
    }

    public function toggle(Promotion $promotion)
    {
        $promotion->update([
            'status' => $promotion->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Promotion status updated.');
    }

    public function destroy(Promotion $promotion)
    {
        if ($promotion->claims()->where('status', 'credited')->exists()) {
            return back()->with('error', 'Cannot delete a promotion with credited claims.');
        }

        $promotion->delete();

        return redirect()->route('admin.promotions.index')->with('success', 'Promotion deleted.');
    }

    public function approveClaim(Request $request, PromotionClaim $claim)
    {
        if (!BonusCreditingService::approveClaim($claim, $request->user()->id)) {
            return back()->with('error', 'Claim cannot be approved in its current state.');
        }

        return back()->with('success', 'Claim approved.');
    }

    public function rejectClaim(Request $request, PromotionClaim $claim)
    {
        if (!BonusCreditingService::rejectClaim($claim, $request->user()->id)) {
            return back()->with('error', 'Claim cannot be rejected in its current state.');
        }

        return back()->with('success', 'Claim rejected.');
    }

    public function creditClaim(Request $request, PromotionClaim $claim)
    {
        if ($claim->status !== 'approved') {
            return back()->with('error', 'Claim must be approved before crediting.');
        }

        $success = BonusCreditingService::creditBonus($claim, $request->user()->id);

        if ($success) {
            NotificationService::bonusCredited($claim->fresh());
            return back()->with('success', 'Bonus credited to user.');
        }

        return back()->with('error', 'Failed to credit bonus.');
    }

    public function cancelClaim(PromotionClaim $claim)
    {
        if (!BonusCreditingService::cancelClaim($claim)) {
            return back()->with('error', 'Claim cannot be cancelled in its current state.');
        }

        return back()->with('success', 'Claim cancelled.');
    }
}
