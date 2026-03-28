<?php

declare(strict_types=1);

namespace PhilipRehberger\Money;

enum RoundingMode: string
{
    case HALF_UP = 'half_up';
    case HALF_DOWN = 'half_down';
    case HALF_EVEN = 'half_even';
    case CEILING = 'ceiling';
    case FLOOR = 'floor';

    /**
     * Apply this rounding mode to the given value, rounding to 0 decimal places.
     */
    public function round(float $value): int
    {
        return match ($this) {
            self::HALF_UP => (int) round($value, 0, PHP_ROUND_HALF_UP),
            self::HALF_DOWN => (int) round($value, 0, PHP_ROUND_HALF_DOWN),
            self::HALF_EVEN => (int) round($value, 0, PHP_ROUND_HALF_EVEN),
            self::CEILING => (int) ceil($value),
            self::FLOOR => (int) floor($value),
        };
    }
}
