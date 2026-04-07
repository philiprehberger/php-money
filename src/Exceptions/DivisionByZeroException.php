<?php

declare(strict_types=1);

namespace PhilipRehberger\Money\Exceptions;

class DivisionByZeroException extends InvalidAmountException
{
    public static function create(): self
    {
        return new self('Division by zero is not allowed.');
    }
}
