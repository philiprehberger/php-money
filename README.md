# PHP Money

[![Tests](https://github.com/philiprehberger/php-money/actions/workflows/tests.yml/badge.svg)](https://github.com/philiprehberger/php-money/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/philiprehberger/php-money.svg)](https://packagist.org/packages/philiprehberger/php-money)
[![License](https://img.shields.io/github/license/philiprehberger/php-money)](LICENSE)
[![Sponsor](https://img.shields.io/badge/sponsor-GitHub%20Sponsors-ec6cb9)](https://github.com/sponsors/philiprehberger)

Immutable Money value object with currency support, formatting, arithmetic, and Laravel Eloquent cast.

## Requirements

- PHP 8.2+
- `ext-intl` (for `format()`)
- Laravel 11 or 12 (optional, only for `MoneyCast`)

## Installation

```bash
composer require philiprehberger/php-money
```

### Laravel

The package auto-discovers `MoneyServiceProvider`. No configuration is required.

## Usage

### Creating Money

```php
use PhilipRehberger\Money\Money;

// Static currency factories — amount in smallest unit (cents)
$price   = Money::USD(1999);   // $19.99
$tax     = Money::EUR(1500);   // €15.00
$pence   = Money::GBP(999);    // £9.99

// Generic factory
$amount  = Money::of(500, 'CAD');  // CA$5.00

// Zero value
$nothing = Money::zero('USD');

// Parse a formatted string
$parsed  = Money::parse('$29.99', 'USD');  // Money::USD(2999)
$parsed2 = Money::parse('€1,299.00', 'EUR'); // Money::EUR(129900)
```

### Arithmetic

All arithmetic methods return new `Money` instances and leave the original unchanged.

```php
$subtotal = Money::USD(1000);
$tax      = Money::USD(80);
$discount = Money::USD(150);

$total = $subtotal->add($tax)->subtract($discount);
$total->getAmount(); // 930 (= $9.30)
```

### Comparison

```php
$a = Money::USD(1000);
$b = Money::USD(2000);

$a->equals($b);             // false
$a->lessThan($b);           // true
$a->isZero();               // false
$a->isPositive();           // true
$a->isNegative();           // false
```

### Allocation

```php
// Split $10.00 three ways by ratio
$parts = Money::USD(1000)->allocate([1, 1, 1]);
// [334, 333, 333] — totals exactly 1000

// Split equally (shorthand)
$parts = Money::USD(1000)->allocateEqual(3);
// [334, 333, 333]
```

### Min / Max

```php
$a = Money::USD(500);
$b = Money::USD(200);
$c = Money::USD(800);

Money::min($a, $b, $c)->getAmount(); // 200
Money::max($a, $b, $c)->getAmount(); // 800
```

### Formatting

```php
$price = Money::USD(1234567);

$price->format('en_US');  // "$12,345.67"
$price->format('de_DE');  // "12.345,67 $"
$price->format('fr_FR');  // "12 345,67 $US"
```

### Laravel Eloquent Cast

```php
use PhilipRehberger\Money\Laravel\MoneyCast;

class Product extends Model
{
    protected $casts = [
        'price' => MoneyCast::class,
    ];
}

$product->price = Money::USD(2999);
$product->save();
// Stored as: {"amount":2999,"currency":"USD"}
```

## API

### Money

| Method | Description | Returns |
|--------|-------------|---------|
| `Money::USD(int $amount)` | Create USD instance (and other static currency factories) | `Money` |
| `Money::of(int $amount, string $currency)` | Create instance for any currency code | `Money` |
| `Money::zero(string $currency)` | Create zero-value instance | `Money` |
| `Money::parse(string $value, string $currency)` | Parse a formatted string | `Money` |
| `->getAmount()` | Get amount in smallest unit | `int` |
| `->getCurrency()` | Get Currency instance | `Currency` |
| `->add(Money $other)` | Add two money values | `Money` |
| `->subtract(Money $other)` | Subtract two money values | `Money` |
| `->multiply(int\|float $factor)` | Multiply by a factor | `Money` |
| `->divide(int\|float $divisor)` | Divide by a divisor | `Money` |
| `->percentage(int\|float $percent)` | Calculate a percentage | `Money` |
| `->allocate(int[] $ratios)` | Split proportionally without rounding loss | `Money[]` |
| `->allocateEqual(int $parts)` | Split equally (remainder to first parts) | `Money[]` |
| `Money::min(Money ...$amounts)` | Return the smallest value | `Money` |
| `Money::max(Money ...$amounts)` | Return the largest value | `Money` |
| `->equals(Money $other)` | Check equality | `bool` |
| `->greaterThan(Money $other)` | Greater than comparison | `bool` |
| `->lessThan(Money $other)` | Less than comparison | `bool` |
| `->greaterThanOrEqual(Money $other)` | Greater than or equal comparison | `bool` |
| `->lessThanOrEqual(Money $other)` | Less than or equal comparison | `bool` |
| `->isZero()` | Check if amount is zero | `bool` |
| `->isPositive()` | Check if amount is positive | `bool` |
| `->isNegative()` | Check if amount is negative | `bool` |
| `->format(string $locale = 'en_US')` | Locale-aware formatted string | `string` |
| `->toArray()` | Serialise to array | `array` |

### Exceptions

| Exception | When thrown |
|-----------|-------------|
| `CurrencyMismatchException` | Arithmetic or comparison between different currencies |
| `InvalidAmountException` | Division by zero, unparseable string, empty/negative ratios |
| `InvalidArgumentException` | Unknown currency code, empty currency code, invalid cast input |

## Development

```bash
composer install
vendor/bin/phpunit
vendor/bin/pint --test
vendor/bin/phpstan analyse
```

## License

MIT
