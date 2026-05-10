<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CasinoSession extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'casino_game_id',
        'session_token',
        'bet_amount',
        'win_amount',
        'balance_before',
        'status',
    ];

    protected $casts = [
        'bet_amount' => 'decimal:2',
        'win_amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($session) {
            $session->uuid = $session->uuid ?? Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(CasinoGame::class, 'casino_game_id');
    }
}
