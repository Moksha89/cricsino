<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $games = [
            [
                'uuid' => Str::uuid()->toString(),
                'name' => 'Crash',
                'slug' => 'crash',
                'provider' => 'house',
                'category' => 'house_originals',
                'game_id' => 'house_crash',
                'description' => 'Provably fair crash game',
                'is_live' => false,
                'active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => Str::uuid()->toString(),
                'name' => 'Dice',
                'slug' => 'dice',
                'provider' => 'house',
                'category' => 'house_originals',
                'game_id' => 'house_dice',
                'description' => 'Provably fair dice game',
                'is_live' => false,
                'active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => Str::uuid()->toString(),
                'name' => 'Mines',
                'slug' => 'mines',
                'provider' => 'house',
                'category' => 'house_originals',
                'game_id' => 'house_mines',
                'description' => 'Provably fair mines game',
                'is_live' => false,
                'active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => Str::uuid()->toString(),
                'name' => 'HiLo',
                'slug' => 'hilo',
                'provider' => 'house',
                'category' => 'house_originals',
                'game_id' => 'house_hilo',
                'description' => 'Provably fair hi-lo card game',
                'is_live' => false,
                'active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($games as $game) {
            // Only insert if not already existing (idempotent)
            if (!DB::table('casino_games')->where('slug', $game['slug'])->exists()) {
                DB::table('casino_games')->insert($game);
            }
        }

        // Fix existing casino_sessions with casino_game_id = 0
        $slugToType = [
            'crash' => null,
            'dice' => null,
            'mines' => null,
            'hilo' => null,
        ];

        // Get the fallback game (crash) for records we can't identify
        $crashGame = DB::table('casino_games')->where('slug', 'crash')->first();

        if ($crashGame) {
            // Update all sessions with casino_game_id = 0 to use crash as default
            // (we can't distinguish which game they were for retroactively)
            DB::table('casino_sessions')
                ->where('casino_game_id', 0)
                ->update(['casino_game_id' => $crashGame->id]);
        }
    }

    public function down(): void
    {
        DB::table('casino_games')->whereIn('slug', ['crash', 'dice', 'mines', 'hilo'])->delete();
    }
};
