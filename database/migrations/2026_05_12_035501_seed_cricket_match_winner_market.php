<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $market = \App\Models\Market::firstOrCreate(
            ['sport' => 'cricket', 'category' => 'winner', 'slug' => 'cricket-match-winner'],
            [
                'name' => 'Match Winner',
                'segment' => 'pregame',
                'active' => true,
                'bookie_active' => true,
                'is_default' => true,
            ]
        );

        $bets = [
            ['result' => 'home', 'name' => '{home}', 'sport' => 'cricket'],
            ['result' => 'draw', 'name' => 'Draw', 'sport' => 'cricket'],
            ['result' => 'away', 'name' => '{away}', 'sport' => 'cricket'],
        ];

        foreach ($bets as $bet) {
            \App\Models\Bet::firstOrCreate(
                ['market_id' => $market->id, 'result' => $bet['result']],
                ['name' => $bet['name'], 'sport' => $bet['sport']]
            );
        }
    }

    public function down(): void
    {
        //
    }
};
