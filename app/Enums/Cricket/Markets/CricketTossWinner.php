<?php

namespace App\Enums\Cricket\Markets;

use App\Enums\MarketCategory;
use App\Contracts\BetMarket;
use App\Enums\LeagueSport;
use App\Enums\Market as EnumsMarket;
use App\Enums\Cricket\Outcomes\CricketTossOutcome;
use App\Models\Bet;
use App\Models\Game;
use App\Models\Market;
use Illuminate\Support\Str;

enum CricketTossWinner: string implements BetMarket
{
    case TOSS_WINNER = 'toss_winner';

    public function oddsId(): int
    {
        return 10;
    }

    public function outcomes(): array
    {
        return [CricketTossOutcome::HOME, CricketTossOutcome::AWAY];
    }

    public function name(): string
    {
        return "Toss Winner";
    }

    public function won(Game $game, Bet $bet): bool
    {
        $outcome = CricketTossOutcome::from($bet->result);
        $tossWinner = $game->getScore('toss_winner');
        return match ($outcome) {
            CricketTossOutcome::HOME => $tossWinner == 'home',
            CricketTossOutcome::AWAY => $tossWinner == 'away',
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
                    'type' => EnumsMarket::CRICKET_TOSS_WINNER,
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

    private static function formatMarketName(string $name): string
    {
        return Str::of($name)->replace(['Home', 'Away'], ['{home}', '{away}']);
    }
}
