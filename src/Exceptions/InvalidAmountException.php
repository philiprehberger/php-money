<?php

declare(strict_types=1);

namespace PhilipRehberger\Money\Exceptions;

use InvalidArgumentException;

class InvalidAmountException extends InvalidArgumentException
{
    public static function forString(string $value): self
    {
        return new self(
            sprintf('Cannot parse "%s" as a monetary amount. Expected a numeric string optionally prefixed with a currency symbol.', $value),
        );
    }

    public static function forDivisionByZero(): self
    {
        return new self('Division by zero is not allowed.');
    }

    public static function forEmptyRatios(): self
    {
        return new self('Allocation ratios array must not be empty.');
    }

    public static function forNegativeRatios(): self
    {
        return new self('Allocation ratios must all be non-negative and their sum must be greater than zero.');
    }
}
