<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    use HasFactory;

    const TYPES = ['welcome', 'deposit', 'cashback', 'manual', 'referral'];
    const STATUSES = ['active', 'inactive', 'expired'];

    protected $fillable = [
        'title',
        'code',
        'description',
        'type',
        'status',
        'start_at',
        'end_at',
        'min_deposit',
        'max_bonus',
        'bonus_percent',
        'fixed_bonus_amount',
        'wagering_multiplier',
        'eligible_user_level',
        'claim_limit_per_user',
        'total_claim_limit',
        'terms_text',
        'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'min_deposit' => 'decimal:2',
        'max_bonus' => 'decimal:2',
        'bonus_percent' => 'decimal:2',
        'fixed_bonus_amount' => 'decimal:2',
        'wagering_multiplier' => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(PromotionClaim::class);
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        if ($this->start_at && $this->start_at->isFuture()) {
            return false;
        }
        if ($this->end_at && $this->end_at->isPast()) {
            return false;
        }
        return true;
    }

    public function isClaimable(): bool
    {
        if (!$this->isActive()) {
            return false;
        }
        if ($this->total_claim_limit !== null) {
            $totalClaims = $this->claims()->whereNotIn('status', ['rejected', 'cancelled'])->count();
            if ($totalClaims >= $this->total_claim_limit) {
                return false;
            }
        }
        return true;
    }

    public function canUserClaim(User $user): bool
    {
        if (!$this->isClaimable()) {
            return false;
        }
        if ($this->eligible_user_level && $user->level !== $this->eligible_user_level) {
            return false;
        }
        if ($this->claim_limit_per_user !== null) {
            $userClaims = $this->claims()
                ->where('user_id', $user->id)
                ->whereNotIn('status', ['rejected', 'cancelled'])
                ->count();
            if ($userClaims >= $this->claim_limit_per_user) {
                return false;
            }
        }
        return true;
    }

    public function calculateBonus(float $depositAmount = 0): float
    {
        $bonus = 0;
        if ($this->fixed_bonus_amount) {
            $bonus = (float) $this->fixed_bonus_amount;
        } elseif ($this->bonus_percent && $depositAmount > 0) {
            $bonus = $depositAmount * ((float) $this->bonus_percent / 100);
        }
        if ($this->max_bonus && $bonus > (float) $this->max_bonus) {
            $bonus = (float) $this->max_bonus;
        }
        return round($bonus, 2);
    }
}
