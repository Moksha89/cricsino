<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoreRefreshLog extends Model
{
    protected $fillable = [
        'sport_key',
        'games_updated',
        'games_skipped',
        'errors_count',
        'status',
        'error_message',
        'triggered_by',
        'trigger_source',
    ];

    public function triggeredByUser()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
