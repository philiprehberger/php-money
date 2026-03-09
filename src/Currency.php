<?php

declare(strict_types=1);

namespace PhilipRehberger\Money;

use InvalidArgumentException;
use Stringable;

/**
 * Immutable currency value object.
 *
 * Provides a registry of the most common ISO 4217 currencies with their
 * symbols and decimal place counts. Custom currencies can be constructed
 * directly via the constructor.
 */
final class Currency implements Stringable
{
    /**
     * Registry of well-known currencies.
     * Format: 'CODE' => [decimalPlaces, symbol]
     *
     * @var array<string, array{0: int, 1: string}>
     */
    private static array $registry = [
        'AED' => [2, 'د.إ'],
        'AUD' => [2, 'A$'],
        'BRL' => [2, 'R$'],
        'CAD' => [2, 'CA$'],
        'CHF' => [2, 'CHF'],
        'CNY' => [2, '¥'],
        'CZK' => [2, 'Kč'],
        'DKK' => [2, 'kr'],
        'EUR' => [2, '€'],
        'GBP' => [2, '£'],
        'HKD' => [2, 'HK$'],
        'HUF' => [2, 'Ft'],
        'INR' => [2, '₹'],
        'JPY' => [0, '¥'],
        'KRW' => [0, '₩'],
        'MXN' => [2, 'MX$'],
        'NOK' => [2, 'kr'],
        'NZD' => [2, 'NZ$'],
        'PLN' => [2, 'zł'],
        'SEK' => [2, 'kr'],
        'SGD' => [2, 'S$'],
        'THB' => [2, '฿'],
        'TRY' => [2, '₺'],
        'USD' => [2, '$'],
        'ZAR' => [2, 'R'],
    ];

    public function __construct(
        private readonly string $code,
        private readonly int $decimalPlaces = 2,
        private readonly string $symbol = '',
    ) {
        if (trim($code) === '') {
            throw new InvalidArgumentException('Currency code must not be empty.');
        }
    }

    // -------------------------------------------------------------------------
    // Static factory helpers for well-known currencies
    // -------------------------------------------------------------------------

    public static function AED(): self
    {
        return self::fromCode('AED');
    }

    public static function AUD(): self
    {
        return self::fromCode('AUD');
    }

    public static function BRL(): self
    {
        return self::fromCode('BRL');
    }

    public static function CAD(): self
    {
        return self::fromCode('CAD');
    }

    public static function CHF(): self
    {
        return self::fromCode('CHF');
    }

    public static function CNY(): self
    {
        return self::fromCode('CNY');
    }

    public static function CZK(): self
    {
        return self::fromCode('CZK');
    }

    public static function DKK(): self
    {
        return self::fromCode('DKK');
    }

    public static function EUR(): self
    {
        return self::fromCode('EUR');
    }

    public static function GBP(): self
    {
        return self::fromCode('GBP');
    }

    public static function HKD(): self
    {
        return self::fromCode('HKD');
    }

    public static function HUF(): self
    {
        return self::fromCode('HUF');
    }

    public static function INR(): self
    {
        return self::fromCode('INR');
    }

    public static function JPY(): self
    {
        return self::fromCode('JPY');
    }

    public static function KRW(): self
    {
        return self::fromCode('KRW');
    }

    public static function MXN(): self
    {
        return self::fromCode('MXN');
    }

    public static function NOK(): self
    {
        return self::fromCode('NOK');
    }

    public static function NZD(): self
    {
        return self::fromCode('NZD');
    }

    public static function PLN(): self
    {
        return self::fromCode('PLN');
    }

    public static function SEK(): self
    {
        return self::fromCode('SEK');
    }

    public static function SGD(): self
    {
        return self::fromCode('SGD');
    }

    public static function THB(): self
    {
        return self::fromCode('THB');
    }

    public static function TRY(): self
    {
        return self::fromCode('TRY');
    }

    public static function USD(): self
    {
        return self::fromCode('USD');
    }

    public static function ZAR(): self
    {
        return self::fromCode('ZAR');
    }

    /**
     * Look up a currency from the built-in registry by its ISO 4217 code.
     *
     * @throws InvalidArgumentException when the code is not in the registry
     */
    public static function fromCode(string $code): self
    {
        $upper = strtoupper(trim($code));

        if (! isset(self::$registry[$upper])) {
            throw new InvalidArgumentException(
                sprintf('Unknown currency code "%s". Use the constructor to define custom currencies.', $upper),
            );
        }

        [$decimalPlaces, $symbol] = self::$registry[$upper];

        return new self($upper, $decimalPlaces, $symbol);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getCode(): string
    {
        return $this->code;
    }

    public function getDecimalPlaces(): int
    {
        return $this->decimalPlaces;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function equals(self $other): bool
    {
        return $this->code === $other->code;
    }

    // -------------------------------------------------------------------------
    // Interfaces
    // -------------------------------------------------------------------------

    public function __toString(): string
    {
        return $this->code;
    }
}
