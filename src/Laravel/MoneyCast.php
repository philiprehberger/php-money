<?php

declare(strict_types=1);

namespace PhilipRehberger\Money\Laravel;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use PhilipRehberger\Money\Money;

/**
 * Eloquent cast for the Money value object.
 *
 * Stores the monetary value as a JSON string in a single database column:
 *   {"amount": 1999, "currency": "USD"}
 *
 * Usage in a model:
 *
 *   protected $casts = [
 *       'price' => MoneyCast::class,
 *   ];
 *
 * You may also specify a default currency to use when the stored value is a
 * bare integer (for migrating legacy columns):
 *
 *   protected $casts = [
 *       'price' => MoneyCast::class . ':USD',
 *   ];
 *
 * @implements CastsAttributes<Money, Money|array{amount: int, currency: string}|null>
 */
class MoneyCast implements CastsAttributes
{
    public function __construct(
        private readonly string $defaultCurrency = 'USD',
    ) {
    }

    /**
     * Transform the stored database value into a Money instance.
     *
     * @param array<string, mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return Money::of($value, $this->defaultCurrency);
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (! is_array($decoded)) {
                throw new InvalidArgumentException(
                    sprintf('Cannot cast value for attribute "%s": expected JSON object, got "%s".', $key, $value),
                );
            }

            $value = $decoded;
        }

        if (is_array($value)) {
            if (! isset($value['amount'], $value['currency'])) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Cannot cast value for attribute "%s": array must contain "amount" and "currency" keys.',
                        $key,
                    ),
                );
            }

            return Money::of((int) $value['amount'], (string) $value['currency']);
        }

        throw new InvalidArgumentException(
            sprintf('Cannot cast value for attribute "%s": unsupported type %s.', $key, gettype($value)),
        );
    }

    /**
     * Transform the Money instance into a storable JSON string.
     *
     * @param array<string, mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Money) {
            return json_encode($value->toArray(), JSON_THROW_ON_ERROR);
        }

        if (is_array($value) && isset($value['amount'], $value['currency'])) {
            return json_encode([
                'amount' => (int) $value['amount'],
                'currency' => (string) $value['currency'],
            ], JSON_THROW_ON_ERROR);
        }

        throw new InvalidArgumentException(
            sprintf(
                'Cannot store value for attribute "%s": expected a Money instance or an array with "amount" and "currency" keys.',
                $key,
            ),
        );
    }
}
