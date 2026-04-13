<?php

declare(strict_types=1);

namespace PhilipRehberger\Money\Exceptions;

class ParseException extends InvalidAmountException
{
    public static function forString(string $value): self
    {
        return new self(
            sprintf('Cannot parse "%s" as a monetary amount. Expected a numeric string optionally prefixed with a currency symbol.', $value),
        );
    }
}
