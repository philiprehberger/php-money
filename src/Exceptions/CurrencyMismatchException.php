<?php

declare(strict_types=1);

namespace PhilipRehberger\Money\Exceptions;

use InvalidArgumentException;

class CurrencyMismatchException extends InvalidArgumentException
{
    public static function forCurrencies(string $expected, string $actual): self
    {
        return new self(
            sprintf(
                'Currency mismatch: expected %s but got %s. Money arithmetic requires matching currencies.',
                $expected,
                $actual,
            ),
        );
    }
}
