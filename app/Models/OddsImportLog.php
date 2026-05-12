<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OddsImportLog extends Model
{
    protected $fillable = [
        'sport_key',
        'games_imported',
        'odds_imported',
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
