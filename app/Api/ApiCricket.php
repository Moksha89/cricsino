<?php

namespace App\Api;

use App\Enums\LeagueSport;
use App\Enums\Cricket\GameStatus;
use App\Enums\Cricket\ScoreType;
use App\Events\GameUpdated;
use App\Models\Game;
use App\Models\League;
use App\Models\Odd;
use App\Models\Team;
use App\Support\Country;
use App\Support\EventHydrant;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Ixudra\Curl\Facades\Curl;
use Str;

class ApiCricket extends ApiSports
{

    public static function url($url)
    {
        return "https://v1.cricket.api-sports.io/$url";
    }

    public static function sport(): LeagueSport
    {
        return LeagueSport::CRICKET;
    }

    public static function scoreTypes(): array
    {
        return ScoreType::cases();
    }

    public static function ended($status): bool
    {
        return GameStatus::tryFrom(strtoupper($status))?->ended() ?? true;
    }

    /**
     * Update leagues
     * @return void
     */
    public static function updateLeagues()
    {
        $response = Curl::to(static::url('leagues'))
            ->withHeader('x-apisports-key: ' . static::apiKey())
            ->asJsonResponse()
            ->get();
        if (!$response || !isset($response->response)) return;
        foreach ($response->response as $lg) {
            if (!isset($lg->id)) continue;
            League::query()->updateOrCreate([
                'leagueId' => $lg->id,
                'sport' => LeagueSport::CRICKET,
            ], [
                'name' => $lg->name ?? 'Cricket League',
                'description' => $lg->name ?? '',
                'image' => $lg->image ?? null,
                'country' => $lg->country->code ?? $lg->country->name ?? 'INT',
                'season' => $lg->season ?? now()->year,
            ]);
        }
    }

    /**
     * Update live games
     * @param Collection $games
     * @return void
     */
    public static function updateLiveGames(Collection $games): void
    {
        foreach ($games as $game) {
            $apiGame = static::fetchGame($game->gameId);
            if (!$apiGame) continue;
            static::updateGameScores($game, $apiGame);
        }
    }

    /**
     * Fetch a single game from API
     */
    public static function fetchGame($gameId)
    {
        $response = Curl::to(static::url('fixtures'))
            ->withHeader('x-apisports-key: ' . static::apiKey())
            ->withData(['id' => $gameId])
            ->asJsonResponse()
            ->get();
        return $response->response[0] ?? null;
    }

    /**
     * Update game scores from API data
     */
    public static function updateGameScores(Game $game, $apiGame): void
    {
        if (isset($apiGame->scores)) {
            $homeScore = $apiGame->scores->home ?? 0;
            $awayScore = $apiGame->scores->away ?? 0;
            $game->scores()->updateOrCreate(
                ['type' => ScoreType::TOTAL->value, 'team' => 'home'],
                ['score' => $homeScore]
            );
            $game->scores()->updateOrCreate(
                ['type' => ScoreType::TOTAL->value, 'team' => 'away'],
                ['score' => $awayScore]
            );
        }

        if (isset($apiGame->status)) {
            $status = GameStatus::tryFrom(strtoupper($apiGame->status->short ?? 'NS'));
            if ($status) {
                $game->status = $status->value;
                if ($status->ended()) {
                    $game->closed = true;
                    $game->ended = true;
                }
                $game->save();
                GameUpdated::dispatch($game);
            }
        }
    }

    /**
     * Load games from API
     * @param int $from days from now 
     * @param int $to days from now
     */
    public static function loadGames(int $from = 0, int $to = 7)
    {
        $response = Curl::to(static::url('fixtures'))
            ->withHeader('x-apisports-key: ' . static::apiKey())
            ->withData([
                'date' => now()->addDays($from)->format('Y-m-d'),
            ])
            ->asJsonResponse()
            ->get();

        if (!$response || !isset($response->response)) return;

        foreach ($response->response as $fixture) {
            if (!isset($fixture->id)) continue;
            $league = League::where('leagueId', $fixture->league->id ?? 0)
                ->where('sport', LeagueSport::CRICKET)
                ->first();
            if (!$league) continue;

            $homeTeam = Team::firstOrCreate(
                ['teamId' => $fixture->teams->home->id ?? 0, 'sport' => LeagueSport::CRICKET],
                ['name' => $fixture->teams->home->name ?? 'TBA', 'image' => $fixture->teams->home->logo ?? null]
            );
            $awayTeam = Team::firstOrCreate(
                ['teamId' => $fixture->teams->away->id ?? 0, 'sport' => LeagueSport::CRICKET],
                ['name' => $fixture->teams->away->name ?? 'TBA', 'image' => $fixture->teams->away->logo ?? null]
            );

            Game::updateOrCreate(
                ['gameId' => $fixture->id, 'sport' => LeagueSport::CRICKET],
                [
                    'league_id' => $league->id,
                    'home_team_id' => $homeTeam->id,
                    'away_team_id' => $awayTeam->id,
                    'name' => ($homeTeam->name ?? 'Home') . ' vs ' . ($awayTeam->name ?? 'Away'),
                    'startTime' => Carbon::parse($fixture->date ?? now()),
                    'status' => $fixture->status->short ?? 'NS',
                    'active' => true,
                ]
            );
        }
    }
}
