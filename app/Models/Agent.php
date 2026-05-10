<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Agent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'parent_id',
        'role',
        'code',
        'commission_rate',
        'credit_limit',
        'balance',
        'exposure',
        'max_users',
        'max_sub_agents',
        'active',
        'can_create_users',
        'can_manage_bets',
        'can_manage_deposits',
        'allowed_sports',
        'settings',
        'last_login_at',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:4',
        'credit_limit' => 'decimal:2',
        'balance' => 'decimal:2',
        'exposure' => 'decimal:2',
        'active' => 'boolean',
        'can_create_users' => 'boolean',
        'can_manage_bets' => 'boolean',
        'can_manage_deposits' => 'boolean',
        'allowed_sports' => 'array',
        'settings' => 'array',
        'last_login_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($agent) {
            $agent->uuid = $agent->uuid ?? Str::uuid();
            $agent->code = $agent->code ?? strtoupper(Str::random(8));
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Agent::class, 'parent_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AgentTransaction::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isMaster(): bool
    {
        return $this->role === 'master';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function getHierarchy(): array
    {
        $hierarchy = [$this];
        $current = $this;
        while ($current->parent) {
            $current = $current->parent;
            array_unshift($hierarchy, $current);
        }
        return $hierarchy;
    }

    public function getTotalUsers(): int
    {
        $count = $this->users()->count();
        foreach ($this->children as $child) {
            $count += $child->getTotalUsers();
        }
        return $count;
    }

    public function creditBalance(float $amount, string $description = null): void
    {
        $balanceBefore = $this->balance;
        $this->increment('balance', $amount);
        $this->transactions()->create([
            'uuid' => Str::uuid(),
            'type' => 'credit',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->fresh()->balance,
            'description' => $description,
        ]);
    }

    public function debitBalance(float $amount, string $description = null): void
    {
        $balanceBefore = $this->balance;
        $this->decrement('balance', $amount);
        $this->transactions()->create([
            'uuid' => Str::uuid(),
            'type' => 'debit',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->fresh()->balance,
            'description' => $description,
        ]);
    }
}
