<?php

namespace App\Enums\Cricket;

enum ScoreType: string
{
    case TOTAL = 'total';
    case FIRST_INNINGS = 'first_innings';
    case SECOND_INNINGS = 'second_innings';
    case THIRD_INNINGS = 'third_innings';
    case FOURTH_INNINGS = 'fourth_innings';
    case WICKETS = 'wickets';
    case OVERS = 'overs';
    case RUN_RATE = 'run_rate';
    case EXTRAS = 'extras';
    case FOURS = 'fours';
    case SIXES = 'sixes';

    public function name()
    {
        return match ($this) {
            static::TOTAL => 'Total Runs',
            static::FIRST_INNINGS => '1st Innings Runs',
            static::SECOND_INNINGS => '2nd Innings Runs',
            static::THIRD_INNINGS => '3rd Innings Runs',
            static::FOURTH_INNINGS => '4th Innings Runs',
            static::WICKETS => 'Wickets',
            static::OVERS => 'Overs',
            static::RUN_RATE => 'Run Rate',
            static::EXTRAS => 'Extras',
            static::FOURS => 'Fours',
            static::SIXES => 'Sixes',
        };
    }
}
