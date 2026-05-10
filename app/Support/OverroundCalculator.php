<?php

namespace  App\Support;

class OverroundCalculator
{
    public static function calculateOverround(array $odds): float
    {
        $impliedProbabilities = array_map(function ($odd) {
            return (1 / $odd) * 100;
        }, $odds);

        $overround = array_sum($impliedProbabilities);

        return round($overround, 2);
    }

    public static function calculateFairOdds(array $odds): array
    {
        $overround = self::calculateOverround($odds);
        $fairOdds = array_map(function ($odd) use ($overround) {
            $impliedProbability = (1 / $odd) * 100;
            $fairProbability = ($impliedProbability / $overround) * 100;
            return round(100 / $fairProbability, 2);
        }, $odds);

        return $fairOdds;
    }
}
