<?php

namespace App\Enums\Cricket\Outcomes;

enum CricketSessionRunsOutcome: string
{
    case OVER_6_RUNS = 'over_6_session';
    case UNDER_6_RUNS = 'under_6_session';
    case OVER_10_RUNS = 'over_10_session';
    case UNDER_10_RUNS = 'under_10_session';
    case OVER_15_RUNS = 'over_15_session';
    case UNDER_15_RUNS = 'under_15_session';
    case OVER_20_RUNS = 'over_20_session';
    case UNDER_20_RUNS = 'under_20_session';
    case OVER_25_RUNS = 'over_25_session';
    case UNDER_25_RUNS = 'under_25_session';

    public function name(): string
    {
        return match ($this) {
            self::OVER_6_RUNS => 'Over 6.5 Runs',
            self::UNDER_6_RUNS => 'Under 6.5 Runs',
            self::OVER_10_RUNS => 'Over 10.5 Runs',
            self::UNDER_10_RUNS => 'Under 10.5 Runs',
            self::OVER_15_RUNS => 'Over 15.5 Runs',
            self::UNDER_15_RUNS => 'Under 15.5 Runs',
            self::OVER_20_RUNS => 'Over 20.5 Runs',
            self::UNDER_20_RUNS => 'Under 20.5 Runs',
            self::OVER_25_RUNS => 'Over 25.5 Runs',
            self::UNDER_25_RUNS => 'Under 25.5 Runs',
        };
    }
}
