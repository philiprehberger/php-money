<?php

declare(strict_types=1);

namespace PhilipRehberger\Money;

use JsonSerializable;
use NumberFormatter;
use PhilipRehberger\Money\Exceptions\CurrencyMismatchException;
use PhilipRehberger\Money\Exceptions\InvalidAmountException;
use Stringable;

/**
 * Immutable monetary value object.
 *
 * Amounts are always stored as integers in the smallest currency unit
 * (e.g. cents for USD/EUR). All arithmetic operations return new instances,
 * leaving the original unchanged.
 *
 * @phpstan-type MoneyArray array{amount: int, currency: string}
 */
final class Money implements JsonSerializable, Stringable
{
    /**
     * @param int $amount Amount in the smallest unit (e.g. cents)
     * @param Currency $currency The currency of this money value
     */
    public function __construct(
        private readonly int $amount,
        private readonly Currency $currency,
    ) {
    }

    // -------------------------------------------------------------------------
    // Static factories
    // -------------------------------------------------------------------------

    /**
     * Create a USD money value from cents.
     */
    public static function USD(int $cents): self
    {
        return new self($cents, Currency::USD());
    }

    /**
     * Create a EUR money value from cents.
     */
    public static function EUR(int $cents): self
    {
        return new self($cents, Currency::EUR());
    }

    /**
     * Create a GBP money value from pence.
     */
    public static function GBP(int $pence): self
    {
        return new self($pence, Currency::GBP());
    }

    /**
     * Create a JPY money value from yen (0 decimal places).
     */
    public static function JPY(int $yen): self
    {
        return new self($yen, Currency::JPY());
    }

    /**
     * Return the smallest of the given Money values.
     *
     * All values must share the same currency.
     *
     * @throws CurrencyMismatchException
     * @throws InvalidAmountException when no arguments are provided
     */
    public static function min(self ...$amounts): self
    {
        if (count($amounts) === 0) {
            throw InvalidAmountException::forEmptyArguments('min');
        }

        $min = $amounts[0];

        for ($i = 1; $i < count($amounts); $i++) {
            $min->assertSameCurrency($amounts[$i]);

            if ($amounts[$i]->amount < $min->amount) {
                $min = $amounts[$i];
            }
        }

        return $min;
    }

    /**
     * Return the largest of the given Money values.
     *
     * All values must share the same currency.
     *
     * @throws CurrencyMismatchException
     * @throws InvalidAmountException when no arguments are provided
     */
    public static function max(self ...$amounts): self
    {
        if (count($amounts) === 0) {
            throw InvalidAmountException::forEmptyArguments('max');
        }

        $max = $amounts[0];

        for ($i = 1; $i < count($amounts); $i++) {
            $max->assertSameCurrency($amounts[$i]);

            if ($amounts[$i]->amount > $max->amount) {
                $max = $amounts[$i];
            }
        }

        return $max;
    }

    /**
     * Create a money value from an amount (in smallest unit) and a currency code string.
     */
    public static function of(int $amount, string $currencyCode): self
    {
        return new self($amount, Currency::fromCode($currencyCode));
    }

    /**
     * Create a zero-value money instance for the given currency.
     */
    public static function zero(string $currencyCode = 'USD'): self
    {
        return new self(0, Currency::fromCode($currencyCode));
    }

    /**
     * Parse a formatted money string into a Money instance.
     *
     * Strips known currency symbols and thousands separators, then converts
     * the decimal representation to the smallest unit.
     *
     * Example:
     *   Money::parse('$19.99', 'USD')  → Money::USD(1999)
     *   Money::parse('€1.500,50', 'EUR') is not supported; pass normalised strings.
     *
     * @throws InvalidAmountException when the string cannot be parsed
     */
    public static function parse(string $formatted, string $currencyCode): self
    {
        $currency = Currency::fromCode($currencyCode);

        // Strip whitespace
        $cleaned = trim($formatted);

        // Strip any leading/trailing currency symbols or codes (non-numeric, non-.-,-)
        // This handles $, €, £, ¥, ₹, etc. as well as letter codes like USD
        $cleaned = preg_replace('/[^0-9.,\-]/', '', $cleaned) ?? '';

        // Remove thousands separators (commas when followed by exactly 3 digits before another comma or end)
        // We handle the simple case: remove commas that appear to be thousands separators
        $cleaned = str_replace(',', '', $cleaned);

        if (! is_numeric($cleaned) || trim($cleaned) === '') {
            throw InvalidAmountException::forString($formatted);
        }

        $floatValue = (float) $cleaned;
        $multiplier = 10 ** $currency->getDecimalPlaces();
        $amount = (int) round($floatValue * $multiplier, 0, PHP_ROUND_HALF_UP);

        return new self($amount, $currency);
    }

    // -------------------------------------------------------------------------
    // Arithmetic (all return new Money instances)
    // -------------------------------------------------------------------------

    /**
     * Add another money value. Both must share the same currency.
     *
     * @throws CurrencyMismatchException
     */
    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount + $other->amount, $this->currency);
    }

    /**
     * Subtract another money value. Both must share the same currency.
     *
     * @throws CurrencyMismatchException
     */
    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount - $other->amount, $this->currency);
    }

    /**
     * Multiply by a numeric factor. The result is rounded half-up.
     */
    public function multiply(int|float $factor): self
    {
        $result = (int) round($this->amount * $factor, 0, PHP_ROUND_HALF_UP);

        return new self($result, $this->currency);
    }

    /**
     * Divide by a numeric divisor. The result is rounded half-up.
     *
     * @throws InvalidAmountException when divisor is zero
     */
    public function divide(int|float $divisor): self
    {
        if ((float) $divisor === 0.0) {
            throw InvalidAmountException::forDivisionByZero();
        }

        $result = (int) round($this->amount / $divisor, 0, PHP_ROUND_HALF_UP);

        return new self($result, $this->currency);
    }

    /**
     * Calculate a percentage of this money value. Equivalent to multiply($percent / 100).
     *
     * Example: Money::USD(1000)->percentage(10.5) // $1.05 (105 cents)
     */
    public function percentage(float $percent): self
    {
        return $this->multiply($percent / 100);
    }

    /**
     * Allocate this money among the given ratios without losing a single cent.
     *
     * Remainders are distributed one unit at a time to the parties with the
     * largest fractional parts (standard "largest-remainder" method).
     *
     * Example: Money::USD(1000)->allocate([1, 1, 1])
     *   → [Money::USD(334), Money::USD(333), Money::USD(333)]
     *
     * @param array<int|float> $ratios
     *
     * @throws InvalidAmountException when ratios array is empty or all-zero/negative
     *
     * @return self[]
     */
    public function allocate(array $ratios): array
    {
        if (empty($ratios)) {
            throw InvalidAmountException::forEmptyRatios();
        }

        $total = array_sum($ratios);

        if ($total <= 0) {
            throw InvalidAmountException::forNegativeRatios();
        }

        foreach ($ratios as $ratio) {
            if ($ratio < 0) {
                throw InvalidAmountException::forNegativeRatios();
            }
        }

        $results = [];
        $allocated = 0;
        $fractions = [];

        foreach ($ratios as $index => $ratio) {
            $exact = $this->amount * $ratio / $total;
            $floored = (int) floor($exact);
            $results[$index] = $floored;
            $allocated += $floored;
            $fractions[$index] = $exact - $floored;
        }

        // Distribute the remainder one unit at a time to indices with the largest fractional parts
        $remainder = $this->amount - $allocated;

        arsort($fractions);

        foreach (array_keys($fractions) as $index) {
            if ($remainder <= 0) {
                break;
            }

            $results[$index]++;
            $remainder--;
        }

        // Restore original order
        ksort($results);

        return array_map(
            fn (int $amount) => new self($amount, $this->currency),
            $results,
        );
    }

    /**
     * Allocate this money equally among the given number of parts.
     *
     * Shorthand for `allocate(array_fill(0, $parts, 1))`. Any remainder
     * cents are distributed one per part starting from the first.
     *
     * @throws InvalidAmountException when parts is less than 1
     *
     * @return self[]
     */
    public function allocateEqual(int $parts): array
    {
        if ($parts < 1) {
            throw InvalidAmountException::forInvalidParts();
        }

        return $this->allocate(array_fill(0, $parts, 1));
    }

    // -------------------------------------------------------------------------
    // Comparison
    // -------------------------------------------------------------------------

    /**
     * @throws CurrencyMismatchException
     */
    public function equals(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->amount === $other->amount;
    }

    /**
     * @throws CurrencyMismatchException
     */
    public function greaterThan(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->amount > $other->amount;
    }

    /**
     * @throws CurrencyMismatchException
     */
    public function lessThan(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->amount < $other->amount;
    }

    /**
     * @throws CurrencyMismatchException
     */
    public function greaterThanOrEqual(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->amount >= $other->amount;
    }

    /**
     * @throws CurrencyMismatchException
     */
    public function lessThanOrEqual(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->amount <= $other->amount;
    }

    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    public function isNegative(): bool
    {
        return $this->amount < 0;
    }

    // -------------------------------------------------------------------------
    // Formatting
    // -------------------------------------------------------------------------

    /**
     * Format the money value using locale-aware number formatting.
     *
     * When the `ext-intl` extension is available (required), NumberFormatter
     * is used for proper locale-aware output. The locale defaults to the
     * current system locale or 'en_US' as fallback.
     *
     * @param string|null $locale A BCP 47 locale string (e.g. 'en_US', 'de_DE', 'fr_FR').
     *                            Defaults to 'en_US'.
     */
    public function format(?string $locale = null): string
    {
        $locale ??= 'en_US';
        $decimalPlaces = $this->currency->getDecimalPlaces();
        $divisor = 10 ** $decimalPlaces;
        $floatAmount = $this->amount / $divisor;

        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

        $formatted = $formatter->formatCurrency($floatAmount, $this->currency->getCode());

        if ($formatted === false) {
            // Fallback: manual formatting
            return $this->formatFallback($floatAmount, $decimalPlaces);
        }

        return $formatted;
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * Return a plain array representation of this money value.
     *
     * @return MoneyArray
     */
    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency->getCode(),
        ];
    }

    // -------------------------------------------------------------------------
    // Interfaces
    // -------------------------------------------------------------------------

    /**
     * @return MoneyArray
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        return $this->format();
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * @throws CurrencyMismatchException
     */
    private function assertSameCurrency(self $other): void
    {
        if (! $this->currency->equals($other->currency)) {
            throw CurrencyMismatchException::forCurrencies(
                $this->currency->getCode(),
                $other->currency->getCode(),
            );
        }
    }

    private function formatFallback(float $amount, int $decimalPlaces): string
    {
        $symbol = $this->currency->getSymbol();
        $number = number_format($amount, $decimalPlaces);

        return $symbol !== '' ? $symbol . $number : $number . ' ' . $this->currency->getCode();
    }
}
