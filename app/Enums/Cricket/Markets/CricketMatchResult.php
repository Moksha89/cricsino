<?php

namespace App\Enums\Cricket\Markets;

use App\Enums\MarketCategory;
use App\Contracts\BetMarket;
use App\Enums\LeagueSport;
use App\Enums\Market as EnumsMarket;
use App\Enums\Cricket\Outcomes\CricketMatchResultOutcome;
use App\Models\Bet;
use App\Models\Game;
use App\Models\Market;
use Illuminate\Support\Str;

enum CricketMatchResult: string implements BetMarket
{
    case THREE_WAY = 'three_way';
    case HOME_AWAY = 'home_away';

    public function oddsId(): int
    {
        return match ($this) {
            self::THREE_WAY => 1,
            self::HOME_AWAY => 2
        };
    }

    public function outcomes(): array
    {
        return match ($this) {
            self::THREE_WAY => [CricketMatchResultOutcome::HOME, CricketMatchResultOutcome::DRAW, CricketMatchResultOutcome::AWAY],
            self::HOME_AWAY => [CricketMatchResultOutcome::HOME, CricketMatchResultOutcome::AWAY],
        };
    }

    public function name(): string
    {
        return match ($this) {
            self::THREE_WAY => "Match Winner",
            self::HOME_AWAY => "Home/Away",
        };
    }

    public function won(Game $game, Bet $bet): bool
    {
        $outcome = CricketMatchResultOutcome::from($bet->result);
        $homeScore = $game->getScore('total', 'home');
        $awayScore = $game->getScore('total', 'away');
        return match ($outcome) {
            CricketMatchResultOutcome::HOME => $homeScore > $awayScore,
            CricketMatchResultOutcome::AWAY => $awayScore > $homeScore,
            CricketMatchResultOutcome::DRAW => $homeScore == $awayScore,
        };
    }

    public static function seed(): void
    {
        foreach (self::cases() as $case) {
            $market = Market::updateOrCreate(
                [
                    'segment' => $case->value,
                    'oddsId' => $case->oddsId(),
                    'sport' => LeagueSport::CRICKET
                ],
                [
                    'slug' => Str::slug($case->name()),
                    'description' => $case->name(),
                    'category' => MarketCategory::getCategory(self::class),
                    'name' => self::formatMarketName($case->name()),
                    'type' => EnumsMarket::CRICKET_MATCH_RESULT,
                    'is_default' => $case == self::THREE_WAY,
                ]
            );
            foreach ($case->outcomes() as $outcome) {
                Bet::updateOrCreate(
                    [
                        'market_id' => $market->id,
                        'name' => $outcome->name(),
                        'sport' => LeagueSport::CRICKET
                    ],
                    ['result' => $outcome->value]
                );
            }
        }
    }

    private static function formatMarketName(string $name): string
    {
        return Str::of($name)->replace(['Home', 'Away'], ['{home}', '{away}']);
    }
}
