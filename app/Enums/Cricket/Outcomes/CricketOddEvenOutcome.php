<?php

namespace App\Enums\Cricket\Outcomes;

enum CricketOddEvenOutcome: string
{
    case ODD = 'odd';
    case EVEN = 'even';

    public function name(): string
    {
        return match ($this) {
            self::ODD => 'Odd',
            self::EVEN => 'Even'
        };
    }
}
