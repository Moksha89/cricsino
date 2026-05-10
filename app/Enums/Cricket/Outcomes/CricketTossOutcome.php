<?php

namespace App\Enums\Cricket\Outcomes;

enum CricketTossOutcome: string
{
    case HOME = 'home_toss';
    case AWAY = 'away_toss';

    public function name(): string
    {
        return match ($this) {
            self::HOME => '{home}',
            self::AWAY => '{away}',
        };
    }
}
