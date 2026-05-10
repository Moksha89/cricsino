<?php

namespace App\Enums\Cricket\Markets;

use App\Enums\MarketCategory;
use App\Contracts\BetMarket;
use App\Enums\LeagueSport;
use App\Enums\Market as EnumsMarket;
use App\Enums\Cricket\Outcomes\CricketSessionRunsOutcome;
use App\Models\Bet;
use App\Models\Game;
use App\Models\Market;
use Illuminate\Support\Str;

enum CricketSessionRuns: string implements BetMarket
{
    case OVER_6 = 'session_over_6';
    case OVER_10 = 'session_over_10';
    case OVER_15 = 'session_over_15';
    case OVER_20 = 'session_over_20';
    case OVER_25 = 'session_over_25';

    public function oddsId(): int
    {
        return match ($this) {
            self::OVER_6 => 30,
            self::OVER_10 => 31,
            self::OVER_15 => 32,
            self::OVER_20 => 33,
            self::OVER_25 => 34,
        };
    }

    public function outcomes(): array
    {
        return match ($this) {
            self::OVER_6 => [CricketSessionRunsOutcome::OVER_6_RUNS, CricketSessionRunsOutcome::UNDER_6_RUNS],
            self::OVER_10 => [CricketSessionRunsOutcome::OVER_10_RUNS, CricketSessionRunsOutcome::UNDER_10_RUNS],
            self::OVER_15 => [CricketSessionRunsOutcome::OVER_15_RUNS, CricketSessionRunsOutcome::UNDER_15_RUNS],
            self::OVER_20 => [CricketSessionRunsOutcome::OVER_20_RUNS, CricketSessionRunsOutcome::UNDER_20_RUNS],
            self::OVER_25 => [CricketSessionRunsOutcome::OVER_25_RUNS, CricketSessionRunsOutcome::UNDER_25_RUNS],
        };
    }

    public function line(): float
    {
        return match ($this) {
            self::OVER_6 => 6.5,
            self::OVER_10 => 10.5,
            self::OVER_15 => 15.5,
            self::OVER_20 => 20.5,
            self::OVER_25 => 25.5,
        };
    }

    public function name(): string
    {
        return match ($this) {
            self::OVER_6 => "Session Runs O/U 6.5",
            self::OVER_10 => "Session Runs O/U 10.5",
            self::OVER_15 => "Session Runs O/U 15.5",
            self::OVER_20 => "Session Runs O/U 20.5",
            self::OVER_25 => "Session Runs O/U 25.5",
        };
    }

    public function won(Game $game, Bet $bet): bool
    {
        $outcome = CricketSessionRunsOutcome::from($bet->result);
        $sessionRuns = (int) ($game->getScore('session_runs') ?? 0);
        $line = $this->line();
        $isOver = str_starts_with($outcome->value, 'over');
        return $isOver ? $sessionRuns > $line : $sessionRuns < $line;
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
                    'type' => EnumsMarket::CRICKET_SESSION_RUNS,
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
