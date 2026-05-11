<?php

namespace Database\Seeders;

use App\Enums\LeagueSport;
use App\Models\Game;
use App\Models\League;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Str;

class DemoSportsSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCricketMatches();
        $this->seedFootballMatches();
        $this->seedBasketballMatches();
    }

    private function seedCricketMatches(): void
    {
        $league = League::firstOrCreate(
            ['name' => 'Indian Premier League', 'sport' => LeagueSport::CRICKET],
            ['slug' => 'indian-premier-league', 'leagueId' => 10001, 'active' => true, 'season' => now()->year]
        );

        $matches = [
            ['Mumbai Indians', 'Chennai Super Kings', 2, true],
            ['Royal Challengers Bangalore', 'Kolkata Knight Riders', 4, false],
            ['Delhi Capitals', 'Rajasthan Royals', 6, false],
            ['Punjab Kings', 'Sunrisers Hyderabad', 8, false],
            ['Gujarat Titans', 'Lucknow Super Giants', 10, false],
            ['Mumbai Indians', 'Royal Challengers Bangalore', 12, false],
            ['Chennai Super Kings', 'Delhi Capitals', 14, false],
            ['Kolkata Knight Riders', 'Punjab Kings', -1, true],
        ];

        foreach ($matches as [$home, $away, $hoursOffset, $isLive]) {
            $homeTeam = Team::firstOrCreate(
                ['name' => $home, 'sport' => LeagueSport::CRICKET],
                ['teamId' => Str::slug($home), 'active' => true]
            );
            $awayTeam = Team::firstOrCreate(
                ['name' => $away, 'sport' => LeagueSport::CRICKET],
                ['teamId' => Str::slug($away), 'active' => true]
            );

            Game::updateOrCreate(
                ['name' => "{$home} vs {$away}", 'sport' => LeagueSport::CRICKET],
                [
                    'gameId' => 'demo_cricket_' . Str::slug("{$home}-vs-{$away}"),
                    'league_id' => $league->id,
                    'home_team_id' => $homeTeam->id,
                    'away_team_id' => $awayTeam->id,
                    'startTime' => now()->addHours($hoursOffset),
                    'active' => true,
                    'is_live' => $isLive,
                    'closed' => false,
                ]
            );
        }

        $t20League = League::firstOrCreate(
            ['name' => 'T20 International', 'sport' => LeagueSport::CRICKET],
            ['slug' => 't20-international', 'leagueId' => 10002, 'active' => true, 'season' => now()->year]
        );

        $t20Matches = [
            ['India', 'Australia', 24, false],
            ['England', 'South Africa', 48, false],
            ['Pakistan', 'New Zealand', 72, false],
        ];

        foreach ($t20Matches as [$home, $away, $hoursOffset, $isLive]) {
            $homeTeam = Team::firstOrCreate(
                ['name' => $home, 'sport' => LeagueSport::CRICKET],
                ['teamId' => Str::slug($home), 'active' => true]
            );
            $awayTeam = Team::firstOrCreate(
                ['name' => $away, 'sport' => LeagueSport::CRICKET],
                ['teamId' => Str::slug($away), 'active' => true]
            );

            Game::updateOrCreate(
                ['name' => "{$home} vs {$away}", 'sport' => LeagueSport::CRICKET],
                [
                    'gameId' => 'demo_t20_' . Str::slug("{$home}-vs-{$away}"),
                    'league_id' => $t20League->id,
                    'home_team_id' => $homeTeam->id,
                    'away_team_id' => $awayTeam->id,
                    'startTime' => now()->addHours($hoursOffset),
                    'active' => true,
                    'is_live' => $isLive,
                    'closed' => false,
                ]
            );
        }
    }

    private function seedFootballMatches(): void
    {
        $league = League::firstOrCreate(
            ['name' => 'English Premier League', 'sport' => LeagueSport::FOOTBALL],
            ['slug' => 'english-premier-league', 'leagueId' => 20001, 'active' => true, 'season' => now()->year]
        );

        $matches = [
            ['Manchester United', 'Liverpool', 3, true],
            ['Arsenal', 'Chelsea', 6, false],
            ['Manchester City', 'Tottenham', 24, false],
            ['Newcastle', 'Aston Villa', 48, false],
        ];

        foreach ($matches as [$home, $away, $hoursOffset, $isLive]) {
            $homeTeam = Team::firstOrCreate(
                ['name' => $home, 'sport' => LeagueSport::FOOTBALL],
                ['teamId' => Str::slug($home), 'active' => true]
            );
            $awayTeam = Team::firstOrCreate(
                ['name' => $away, 'sport' => LeagueSport::FOOTBALL],
                ['teamId' => Str::slug($away), 'active' => true]
            );

            Game::updateOrCreate(
                ['name' => "{$home} vs {$away}", 'sport' => LeagueSport::FOOTBALL],
                [
                    'gameId' => 'demo_epl_' . Str::slug("{$home}-vs-{$away}"),
                    'league_id' => $league->id,
                    'home_team_id' => $homeTeam->id,
                    'away_team_id' => $awayTeam->id,
                    'startTime' => now()->addHours($hoursOffset),
                    'active' => true,
                    'is_live' => $isLive,
                    'closed' => false,
                ]
            );
        }
    }

    private function seedBasketballMatches(): void
    {
        $league = League::firstOrCreate(
            ['name' => 'NBA', 'sport' => LeagueSport::BASKETBALL],
            ['slug' => 'nba', 'leagueId' => 30001, 'active' => true, 'season' => now()->year]
        );

        $matches = [
            ['Los Angeles Lakers', 'Golden State Warriors', 5, true],
            ['Boston Celtics', 'Miami Heat', 24, false],
            ['Milwaukee Bucks', 'Denver Nuggets', 48, false],
        ];

        foreach ($matches as [$home, $away, $hoursOffset, $isLive]) {
            $homeTeam = Team::firstOrCreate(
                ['name' => $home, 'sport' => LeagueSport::BASKETBALL],
                ['teamId' => Str::slug($home), 'active' => true]
            );
            $awayTeam = Team::firstOrCreate(
                ['name' => $away, 'sport' => LeagueSport::BASKETBALL],
                ['teamId' => Str::slug($away), 'active' => true]
            );

            Game::updateOrCreate(
                ['name' => "{$home} vs {$away}", 'sport' => LeagueSport::BASKETBALL],
                [
                    'gameId' => 'demo_nba_' . Str::slug("{$home}-vs-{$away}"),
                    'league_id' => $league->id,
                    'home_team_id' => $homeTeam->id,
                    'away_team_id' => $awayTeam->id,
                    'startTime' => now()->addHours($hoursOffset),
                    'active' => true,
                    'is_live' => $isLive,
                    'closed' => false,
                ]
            );
        }
    }
}
