<?php

namespace App\Support;

class ProvablyFair
{
    public static function generateServerSeed(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function hashServerSeed(string $serverSeed): string
    {
        return hash('sha256', $serverSeed);
    }

    public static function generateResult(string $serverSeed, string $clientSeed, int $nonce): float
    {
        $combined = $serverSeed . ':' . $clientSeed . ':' . $nonce;
        $hash = hash_hmac('sha256', $combined, $serverSeed);
        $hex = substr($hash, 0, 8);
        $int = hexdec($hex);
        return $int / 0xFFFFFFFF;
    }

    public static function crashPoint(string $serverSeed, string $clientSeed, int $nonce, float $houseEdge = 0.03): float
    {
        $result = static::generateResult($serverSeed, $clientSeed, $nonce);

        if ($result < $houseEdge) {
            return 1.0;
        }

        $crashPoint = (1 - $houseEdge) / (1 - $result);
        return round(max(1.0, $crashPoint), 2);
    }

    public static function diceResult(string $serverSeed, string $clientSeed, int $nonce): float
    {
        $result = static::generateResult($serverSeed, $clientSeed, $nonce);
        return round($result * 100, 2);
    }

    public static function minesGrid(string $serverSeed, string $clientSeed, int $nonce, int $mineCount = 5): array
    {
        $grid = array_fill(0, 25, false);
        $positions = [];

        for ($i = 0; $i < $mineCount; $i++) {
            $combined = $serverSeed . ':' . $clientSeed . ':' . $nonce . ':' . $i;
            $hash = hash_hmac('sha256', $combined, $serverSeed);
            $hex = substr($hash, 0, 8);
            $position = hexdec($hex) % 25;

            while (in_array($position, $positions)) {
                $position = ($position + 1) % 25;
            }
            $positions[] = $position;
            $grid[$position] = true;
        }

        return ['grid' => $grid, 'mines' => $positions];
    }

    public static function hiloCard(string $serverSeed, string $clientSeed, int $nonce): array
    {
        $result = static::generateResult($serverSeed, $clientSeed, $nonce);
        $cardIndex = (int) floor($result * 52);

        $suits = ['hearts', 'diamonds', 'clubs', 'spades'];
        $values = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

        $suit = $suits[(int) floor($cardIndex / 13)];
        $value = $values[$cardIndex % 13];
        $numericValue = ($cardIndex % 13) + 1;

        return [
            'suit' => $suit,
            'value' => $value,
            'numeric' => $numericValue,
            'index' => $cardIndex,
        ];
    }
}
