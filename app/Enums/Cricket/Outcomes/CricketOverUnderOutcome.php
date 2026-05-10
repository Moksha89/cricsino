<?php

namespace App\Enums\Cricket\Outcomes;

enum CricketOverUnderOutcome: string
{
    case OVER_150 = 'over_150';
    case UNDER_150 = 'under_150';
    case OVER_200 = 'over_200';
    case UNDER_200 = 'under_200';
    case OVER_250 = 'over_250';
    case UNDER_250 = 'under_250';
    case OVER_300 = 'over_300';
    case UNDER_300 = 'under_300';
    case OVER_350 = 'over_350';
    case UNDER_350 = 'under_350';

    public function name(): string
    {
        return match ($this) {
            self::OVER_150 => 'Over 150.5',
            self::UNDER_150 => 'Under 150.5',
            self::OVER_200 => 'Over 200.5',
            self::UNDER_200 => 'Under 200.5',
            self::OVER_250 => 'Over 250.5',
            self::UNDER_250 => 'Under 250.5',
            self::OVER_300 => 'Over 300.5',
            self::UNDER_300 => 'Under 300.5',
            self::OVER_350 => 'Over 350.5',
            self::UNDER_350 => 'Under 350.5',
        };
    }

    public function value(): float
    {
        return match ($this) {
            self::OVER_150, self::UNDER_150 => 150.5,
            self::OVER_200, self::UNDER_200 => 200.5,
            self::OVER_250, self::UNDER_250 => 250.5,
            self::OVER_300, self::UNDER_300 => 300.5,
            self::OVER_350, self::UNDER_350 => 350.5,
        };
    }

    public function type(): string
    {
        return str_starts_with($this->value, 'over') ? 'over' : 'under';
    }

    public static function getOverUnders(): array
    {
        return [150.5, 200.5, 250.5, 300.5, 350.5];
    }
}
