<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CasinoGame extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'provider',
        'category',
        'game_id',
        'image',
        'description',
        'launch_url',
        'is_live',
        'active',
        'sort_order',
        'meta',
    ];

    protected $casts = [
        'is_live' => 'boolean',
        'active' => 'boolean',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($game) {
            $game->uuid = $game->uuid ?? Str::uuid();
            $game->slug = $game->slug ?? Str::slug($game->name);
        });
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CasinoSession::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeLive($query)
    {
        return $query->where('is_live', true);
    }
}
