<?php

namespace App\Api;

use App\Enums\LeagueSport;
use App\Models\Game;
use App\Models\League;
use App\Models\Odd;
use App\Models\Bet;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Str;

class TheOddsApi
{
    private static string $baseUrl = 'https://api.the-odds-api.com/v4';

    public static function apiKey(): ?string
    {
        return config('services.theoddsapi.apikey', settings('site.theoddsapi_api_key'));
    }

    public static function sportKeyMap(): array
    {
        return [
            'soccer_epl' => LeagueSport::FOOTBALL,
            'soccer_spain_la_liga' => LeagueSport::FOOTBALL,
            'soccer_germany_bundesliga' => LeagueSport::FOOTBALL,
            'soccer_italy_serie_a' => LeagueSport::FOOTBALL,
            'soccer_france_ligue_one' => LeagueSport::FOOTBALL,
            'soccer_uefa_champs_league' => LeagueSport::FOOTBALL,
            'americanfootball_nfl' => LeagueSport::NFL,
            'basketball_nba' => LeagueSport::BASKETBALL,
            'baseball_mlb' => LeagueSport::BASEBALL,
            'icehockey_nhl' => LeagueSport::HOCKEY,
            'mma_mixed_martial_arts' => LeagueSport::MMA,
            'rugbyleague_nrl' => LeagueSport::RUGBY,
            'cricket_ipl' => LeagueSport::CRICKET,
            'cricket_test_match' => LeagueSport::CRICKET,
            'cricket_odi' => LeagueSport::CRICKET,
            'cricket_t20_intl' => LeagueSport::CRICKET,
            'cricket_big_bash' => LeagueSport::CRICKET,
            'tennis_atp_french_open' => LeagueSport::HANDBALL,
        ];
    }

    public static function getSports(): array
    {
        $apiKey = static::apiKey();
        if (!$apiKey) return [];

        $response = Http::get(static::$baseUrl . '/sports', [
            'apiKey' => $apiKey,
        ]);

        if ($response->failed()) {
            Log::error('TheOddsApi: Failed to fetch sports', ['status' => $response->status()]);
            return [];
        }

        return $response->json();
    }

    public static function getOdds(string $sportKey, string $regions = 'uk,eu', string $markets = 'h2h,spreads,totals'): array
    {
        $apiKey = static::apiKey();
        if (!$apiKey) return [];

        $response = Http::get(static::$baseUrl . "/sports/{$sportKey}/odds", [
            'apiKey' => $apiKey,
            'regions' => $regions,
            'markets' => $markets,
            'oddsFormat' => 'decimal',
        ]);

        if ($response->failed()) {
            Log::error('TheOddsApi: Failed to fetch odds', [
                'sport' => $sportKey,
                'status' => $response->status(),
            ]);
            return [];
        }

        return $response->json();
    }

    public static function getScores(string $sportKey, int $daysFrom = 1): array
    {
        $apiKey = static::apiKey();
        if (!$apiKey) return [];

        $response = Http::get(static::$baseUrl . "/sports/{$sportKey}/scores", [
            'apiKey' => $apiKey,
            'daysFrom' => $daysFrom,
        ]);

        if ($response->failed()) return [];
        return $response->json();
    }

    public static function importOdds(string $sportKey): int
    {
        $events = static::getOdds($sportKey);
        if (empty($events)) return 0;

        $sportMap = static::sportKeyMap();
        $sport = $sportMap[$sportKey] ?? null;
        if (!$sport) return 0;

        $count = 0;
        foreach ($events as $event) {
            try {
                $count += static::processEvent($event, $sport, $sportKey);
            } catch (\Exception $e) {
                Log::error('TheOddsApi: Error processing event', [
                    'event_id' => $event['id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    private static function processEvent(array $event, LeagueSport $sport, string $sportKey): int
    {
        $homeTeam = Team::firstOrCreate(
            ['name' => $event['home_team'], 'sport' => $sport],
            ['teamId' => Str::slug($event['home_team']), 'active' => true]
        );

        $awayTeam = Team::firstOrCreate(
            ['name' => $event['away_team'], 'sport' => $sport],
            ['teamId' => Str::slug($event['away_team']), 'active' => true]
        );

        $leagueName = static::leagueNameFromKey($sportKey);
        $league = League::firstOrCreate(
            ['name' => $leagueName, 'sport' => $sport],
            [
                'slug' => Str::slug($leagueName),
                'leagueId' => crc32($sportKey),
                'active' => true,
                'season' => now()->year,
            ]
        );

        $commenceTime = Carbon::parse($event['commence_time']);

        $game = Game::updateOrCreate(
            ['gameId' => $event['id'], 'sport' => $sport],
            [
                'league_id' => $league->id,
                'home_team_id' => $homeTeam->id,
                'away_team_id' => $awayTeam->id,
                'name' => $event['home_team'] . ' vs ' . $event['away_team'],
                'startTime' => $commenceTime,
                'active' => true,
                'is_live' => $commenceTime->isPast() && $commenceTime->diffInHours(now()) < 4,
                'closed' => false,
            ]
        );

        $count = 0;
        foreach ($event['bookmakers'] ?? [] as $bookmaker) {
            foreach ($bookmaker['markets'] ?? [] as $market) {
                $count += static::processMarketOdds($game, $market, $bookmaker['key']);
            }
        }

        return $count;
    }

    private static function processMarketOdds(Game $game, array $market, string $bookmakerKey): int
    {
        $count = 0;
        foreach ($market['outcomes'] ?? [] as $outcome) {
            $betName = $outcome['name'];
            $odds = $outcome['price'] ?? 0;
            $point = $outcome['point'] ?? null;

            if ($odds <= 1) continue;

            $marketModel = Market::where('sport', $game->sport)->first();
            if (!$marketModel) continue;

            $bet = Bet::where('market_id', $marketModel->id)
                ->where('sport', $game->sport)
                ->first();
            if (!$bet) continue;

            $game->markets()->syncWithoutDetaching([
                $marketModel->id => ['uuid' => Str::uuid()]
            ]);

            Odd::updateOrCreate(
                [
                    'game_id' => $game->id,
                    'bet_id' => $bet->id,
                    'market_id' => $marketModel->id,
                    'source' => 'theoddsapi_' . $bookmakerKey,
                ],
                [
                    'odd' => round($odds, 2),
                    'active' => true,
                    'sport' => $game->sport,
                ]
            );

            $count++;
        }

        return $count;
    }

    private static function leagueNameFromKey(string $sportKey): string
    {
        $names = [
            'soccer_epl' => 'English Premier League',
            'soccer_spain_la_liga' => 'La Liga',
            'soccer_germany_bundesliga' => 'Bundesliga',
            'soccer_italy_serie_a' => 'Serie A',
            'soccer_france_ligue_one' => 'Ligue 1',
            'soccer_uefa_champs_league' => 'UEFA Champions League',
            'americanfootball_nfl' => 'NFL',
            'basketball_nba' => 'NBA',
            'baseball_mlb' => 'MLB',
            'icehockey_nhl' => 'NHL',
            'mma_mixed_martial_arts' => 'UFC/MMA',
            'rugbyleague_nrl' => 'NRL',
            'cricket_ipl' => 'Indian Premier League',
            'cricket_test_match' => 'Test Cricket',
            'cricket_odi' => 'ODI Cricket',
            'cricket_t20_intl' => 'T20 International',
            'cricket_big_bash' => 'Big Bash League',
        ];

        return $names[$sportKey] ?? ucwords(str_replace('_', ' ', $sportKey));
    }

    public static function importAllSports(): array
    {
        $results = [];
        $sports = static::getSports();

        foreach ($sports as $sport) {
            if (!($sport['active'] ?? false)) continue;
            $key = $sport['key'];
            if (!isset(static::sportKeyMap()[$key])) continue;

            $count = static::importOdds($key);
            $results[$key] = $count;
        }

        return $results;
    }
}
