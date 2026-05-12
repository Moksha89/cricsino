<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionClaim extends Model
{
    use HasFactory;

    const STATUSES = ['pending', 'approved', 'rejected', 'credited', 'cancelled', 'expired'];

    protected $fillable = [
        'promotion_id',
        'user_id',
        'deposit_id',
        'amount',
        'bonus_amount',
        'status',
        'wagering_required',
        'wagering_completed',
        'approved_by',
        'approved_at',
        'credited_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'wagering_required' => 'decimal:2',
        'wagering_completed' => 'decimal:2',
        'approved_at' => 'datetime',
        'credited_at' => 'datetime',
    ];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function bonusTransactions()
    {
        return $this->hasMany(BonusTransaction::class, 'claim_id');
    }
}
