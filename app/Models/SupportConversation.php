<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SupportConversation extends Model
{
    protected $table = 'support_conversations';

    protected $fillable = [
        'user_id',
        'subject',
        'category',
        'status',
        'priority',
        'last_message_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public const CATEGORIES = [
        'deposit',
        'withdrawal',
        'betting',
        'casino',
        'account',
        'kyc',
        'technical',
        'other',
    ];

    public const STATUSES = [
        'open',
        'pending',
        'answered',
        'closed',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'conversation_id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(SupportMessage::class, 'conversation_id')->latestOfMany();
    }

    public function isOpen(): bool
    {
        return $this->status !== 'closed';
    }
}
