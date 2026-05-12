<?php

namespace App\Api;

use App\Enums\LeagueSport;
use App\Models\Bet;
use App\Models\Game;
use App\Models\League;
use App\Models\Market;
use App\Models\Odd;
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

        $game->load(['homeTeam', 'awayTeam']);

        $count = 0;
        foreach ($event['bookmakers'] ?? [] as $bookmaker) {
            foreach ($bookmaker['markets'] ?? [] as $market) {
                $count += static::processMarketOdds($game, $market, $bookmaker['key'], $league->id);
            }
        }

        return $count;
    }

    private static function marketKeyToCategory(): array
    {
        return [
            'h2h' => 'winner',
            'spreads' => 'handicap',
            'totals' => 'total',
        ];
    }

    private static function processMarketOdds(Game $game, array $market, string $bookmakerKey, int $leagueId = 0): int
    {
        $marketKey = $market['key'] ?? 'h2h';
        $categoryMap = static::marketKeyToCategory();
        $category = $categoryMap[$marketKey] ?? null;
        if (!$category) {
            Log::info('TheOddsApi: Skipping unmapped market', ['key' => $marketKey, 'game' => $game->id]);
            return 0;
        }

        $marketModel = Market::where('sport', $game->sport)
            ->where('category', $category)
            ->first();
        if (!$marketModel) {
            Log::info('TheOddsApi: No market model for category', ['sport' => $game->sport->value, 'category' => $category]);
            return 0;
        }

        $game->markets()->syncWithoutDetaching([
            $marketModel->id => ['uuid' => Str::uuid()]
        ]);

        $count = 0;
        foreach ($market['outcomes'] ?? [] as $outcome) {
            $betName = $outcome['name'];
            $odds = $outcome['price'] ?? 0;

            if ($odds <= 1.01) continue;

            $bet = static::matchBet($marketModel, $game, $betName);
            if (!$bet) {
                Log::info('TheOddsApi: No bet match', ['market' => $marketModel->id, 'outcome' => $betName]);
                continue;
            }

            $md5 = md5($marketModel->id . '-' . $bet->id . '-' . $game->id);

            Odd::updateOrCreate(
                ['md5' => $md5],
                [
                    'game_id' => $game->id,
                    'bet_id' => $bet->id,
                    'market_id' => $marketModel->id,
                    'league_id' => $leagueId,
                    'bookie' => 'theoddsapi_' . $bookmakerKey,
                    'odd' => round($odds, 2),
                    'active' => true,
                ]
            );

            $count++;
        }

        return $count;
    }

    private static function matchBet(Market $marketModel, Game $game, string $outcomeName): ?Bet
    {
        $homeName = $game->homeTeam->name ?? '';
        $awayName = $game->awayTeam->name ?? '';

        if (strcasecmp($outcomeName, $homeName) === 0 || strtolower($outcomeName) === 'home') {
            return Bet::where('market_id', $marketModel->id)->where('result', 'home')->first();
        }
        if (strcasecmp($outcomeName, $awayName) === 0 || strtolower($outcomeName) === 'away') {
            return Bet::where('market_id', $marketModel->id)->where('result', 'away')->first();
        }
        if (strtolower($outcomeName) === 'draw' || strtolower($outcomeName) === 'tie') {
            return Bet::where('market_id', $marketModel->id)->where('result', 'draw')->first();
        }
        if (strtolower($outcomeName) === 'over') {
            return Bet::where('market_id', $marketModel->id)->where('result', 'like', '%over%')->first();
        }
        if (strtolower($outcomeName) === 'under') {
            return Bet::where('market_id', $marketModel->id)->where('result', 'like', '%under%')->first();
        }

        return Bet::where('market_id', $marketModel->id)
            ->where('result', Str::slug($outcomeName, '_'))
            ->first();
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

    public static function importScores(string $sportKey, int $daysFrom = 3): array
    {
        $events = static::getScores($sportKey, $daysFrom);
        if (empty($events)) return ['updated' => 0, 'skipped' => 0, 'errors' => 0];

        $sportMap = static::sportKeyMap();
        $sport = $sportMap[$sportKey] ?? null;
        if (!$sport) return ['updated' => 0, 'skipped' => 0, 'errors' => 0];

        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($events as $event) {
            try {
                $game = Game::where('gameId', $event['id'])
                    ->where('sport', $sport)
                    ->first();

                if (!$game) {
                    $skipped++;
                    continue;
                }

                $scores = $event['scores'] ?? null;
                $completed = $event['completed'] ?? false;
                $lastUpdate = $event['last_update'] ?? null;

                if ($scores && is_array($scores)) {
                    $homeScore = null;
                    $awayScore = null;

                    foreach ($scores as $scoreEntry) {
                        $name = $scoreEntry['name'] ?? '';
                        $score = $scoreEntry['score'] ?? '0';

                        if (strcasecmp($name, $event['home_team'] ?? '') === 0) {
                            $homeScore = $score;
                        } elseif (strcasecmp($name, $event['away_team'] ?? '') === 0) {
                            $awayScore = $score;
                        }
                    }

                    if ($homeScore !== null || $awayScore !== null) {
                        $finalScoreType = $sport->finalScoreType();
                        $game->scores()->updateOrCreate(
                            ['type' => $finalScoreType],
                            [
                                'home' => $homeScore ?? '0',
                                'away' => $awayScore ?? '0',
                            ]
                        );
                    }
                }

                if ($completed && !$game->closed) {
                    $game->closed = true;
                    $game->endTime = $lastUpdate ? Carbon::parse($lastUpdate) : now();
                    $game->save();
                } elseif (!$completed && $scores) {
                    $game->is_live = true;
                    $game->save();
                }

                $updated++;
            } catch (\Exception $e) {
                Log::error('TheOddsApi: Error processing score', [
                    'event_id' => $event['id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
                $errors++;
            }
        }

        return ['updated' => $updated, 'skipped' => $skipped, 'errors' => $errors];
    }
}
