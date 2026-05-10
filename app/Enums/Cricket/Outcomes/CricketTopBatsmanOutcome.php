<?php

namespace App\Enums\Cricket\Outcomes;

enum CricketTopBatsmanOutcome: string
{
    case PLAYER_1 = 'player_1';
    case PLAYER_2 = 'player_2';
    case PLAYER_3 = 'player_3';
    case PLAYER_4 = 'player_4';
    case PLAYER_5 = 'player_5';
    case OTHER = 'other';

    public function name(): string
    {
        return match ($this) {
            self::PLAYER_1 => 'Player 1',
            self::PLAYER_2 => 'Player 2',
            self::PLAYER_3 => 'Player 3',
            self::PLAYER_4 => 'Player 4',
            self::PLAYER_5 => 'Player 5',
            self::OTHER => 'Other',
        };
    }
}
