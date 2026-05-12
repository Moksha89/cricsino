<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonusTransaction extends Model
{
    use HasFactory;

    const TYPES = ['credit', 'debit', 'expire', 'adjustment'];

    protected $fillable = [
        'user_id',
        'promotion_id',
        'claim_id',
        'amount',
        'type',
        'balance_before',
        'balance_after',
        'description',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(PromotionClaim::class, 'claim_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
