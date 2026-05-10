<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AgentTransaction extends Model
{
    protected $fillable = [
        'uuid',
        'agent_id',
        'user_id',
        'related_agent_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($transaction) {
            $transaction->uuid = $transaction->uuid ?? Str::uuid();
        });
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function relatedAgent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'related_agent_id');
    }
}
