<?php

namespace App\Enums\Cricket;

use App\Contracts\GameStatus as ContractsGameStatus;

enum GameStatus: string implements ContractsGameStatus
{
    case NotStarted = 'NS';
    case TossComplete = 'TOSS';
    case FirstInnings = '1ST';
    case InningsBreak = 'BRK';
    case SecondInnings = '2ND';
    case ThirdInnings = '3RD';
    case FourthInnings = '4TH';
    case Drinks = 'DRK';
    case Lunch = 'LUN';
    case Tea = 'TEA';
    case Stumps = 'STM';
    case RainDelay = 'RAIN';
    case Finished = 'FT';
    case Abandoned = 'ABD';
    case Cancelled = 'CANC';
    case Postponed = 'PST';
    case SuperOver = 'SO';
    case Review = 'DRS';
    case InProgress = 'LIVE';

    public function description(): string
    {
        return match ($this) {
            self::NotStarted => 'Not Started',
            self::TossComplete => 'Toss Complete',
            self::FirstInnings => '1st Innings',
            self::InningsBreak => 'Innings Break',
            self::SecondInnings => '2nd Innings',
            self::ThirdInnings => '3rd Innings',
            self::FourthInnings => '4th Innings',
            self::Drinks => 'Drinks Break',
            self::Lunch => 'Lunch Break',
            self::Tea => 'Tea Break',
            self::Stumps => 'Stumps',
            self::RainDelay => 'Rain Delay',
            self::Finished => 'Match Finished',
            self::Abandoned => 'Match Abandoned',
            self::Cancelled => 'Match Cancelled',
            self::Postponed => 'Match Postponed',
            self::SuperOver => 'Super Over',
            self::Review => 'DRS Review',
            self::InProgress => 'Live',
        };
    }

    public function gameState(): string
    {
        return match ($this) {
            self::NotStarted, self::TossComplete => 'scheduled',
            self::FirstInnings, self::SecondInnings, self::ThirdInnings, self::FourthInnings,
            self::InningsBreak, self::Drinks, self::Lunch, self::Tea, self::Stumps,
            self::RainDelay, self::SuperOver, self::Review, self::InProgress => 'in_progress',
            self::Finished => 'finished',
            self::Abandoned, self::Cancelled => 'cancelled',
            self::Postponed => 'postponed',
        };
    }

    public function statusText(): string
    {
        return match ($this) {
            self::NotStarted => 'Not Started',
            self::TossComplete => 'Toss',
            self::FirstInnings => '1st Inn',
            self::SecondInnings => '2nd Inn',
            self::ThirdInnings => '3rd Inn',
            self::FourthInnings => '4th Inn',
            self::InningsBreak => 'Break',
            self::Drinks => 'Drinks',
            self::Lunch => 'Lunch',
            self::Tea => 'Tea',
            self::Stumps => 'Stumps',
            self::RainDelay => 'Rain',
            self::Finished => 'Finished',
            self::Abandoned => 'Abandoned',
            self::Cancelled => 'Cancelled',
            self::Postponed => 'Postponed',
            self::SuperOver => 'Super Over',
            self::Review => 'DRS',
            self::InProgress => 'Live',
        };
    }

    public function ended(): bool
    {
        return match ($this) {
            self::Finished, self::Abandoned, self::Cancelled, self::Postponed => true,
            default => false
        };
    }

    public function finished(): bool
    {
        return match ($this) {
            self::Finished => true,
            default => false
        };
    }

    public function cancelled(): bool
    {
        return match ($this) {
            self::Abandoned, self::Cancelled, self::Postponed => true,
            default => false
        };
    }
}
