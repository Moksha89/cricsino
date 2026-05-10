<?php

namespace App\Enums\Cricket\Markets;

use App\Enums\MarketCategory;
use App\Contracts\BetMarket;
use App\Enums\LeagueSport;
use App\Enums\Market as EnumsMarket;
use App\Enums\Cricket\Outcomes\CricketOddEvenOutcome;
use App\Models\Bet;
use App\Models\Game;
use App\Models\Market;
use Illuminate\Support\Str;

enum CricketOddEven: string implements BetMarket
{
    case TOTAL_RUNS = 'total_runs_odd_even';
    case FIRST_INNINGS = 'first_innings_odd_even';

    public function oddsId(): int
    {
        return match ($this) {
            self::TOTAL_RUNS => 20,
            self::FIRST_INNINGS => 21,
        };
    }

    public function outcomes(): array
    {
        return [CricketOddEvenOutcome::ODD, CricketOddEvenOutcome::EVEN];
    }

    public function name(): string
    {
        return match ($this) {
            self::TOTAL_RUNS => "Total Runs Odd/Even",
            self::FIRST_INNINGS => "1st Innings Odd/Even",
        };
    }

    public function won(Game $game, Bet $bet): bool
    {
        $outcome = CricketOddEvenOutcome::from($bet->result);
        $scoreKey = match ($this) {
            self::TOTAL_RUNS => 'total',
            self::FIRST_INNINGS => 'first_innings',
        };
        $totalRuns = (int) ($game->getScore($scoreKey, 'home') + $game->getScore($scoreKey, 'away'));
        $isOdd = $totalRuns % 2 !== 0;
        return match ($outcome) {
            CricketOddEvenOutcome::ODD => $isOdd,
            CricketOddEvenOutcome::EVEN => !$isOdd,
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
                    'name' => $case->name(),
                    'type' => EnumsMarket::CRICKET_ODD_EVEN,
                    'is_default' => false,
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
}
