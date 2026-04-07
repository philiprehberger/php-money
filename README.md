# PHP Money

[![Tests](https://github.com/philiprehberger/php-money/actions/workflows/tests.yml/badge.svg)](https://github.com/philiprehberger/php-money/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/philiprehberger/php-money.svg)](https://packagist.org/packages/philiprehberger/php-money)
[![Last updated](https://img.shields.io/github/last-commit/philiprehberger/php-money)](https://github.com/philiprehberger/php-money/commits/main)

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

### Collection Operations

```php
$a = Money::USD(1000);
$b = Money::USD(2000);
$c = Money::USD(3000);

Money::sum($a, $b, $c)->getAmount();     // 6000
Money::avg($a, $b, $c)->getAmount();     // 2000
Money::minimum($a, $b, $c)->getAmount(); // 1000
Money::maximum($a, $b, $c)->getAmount(); // 3000
```

### Rounding Modes

```php
use PhilipRehberger\Money\RoundingMode;

$price = Money::USD(1000);

$price->multiply(1.005);                            // 1005 (HALF_UP default)
$price->multiply(1.005, RoundingMode::HALF_DOWN);   // 1005
$price->multiply(1.005, RoundingMode::FLOOR);       // 1005
$price->multiply(1.005, RoundingMode::CEILING);     // 1005
```

### Currency Conversion

```php
$usd = Money::USD(10000); // $100.00
$eur = $usd->convertTo(Currency::EUR(), 0.85); // €85.00
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
| `Money::sum(Money $first, Money ...$rest)` | Sum all money values | `Money` |
| `Money::avg(Money $first, Money ...$rest)` | Average of all money values | `Money` |
| `Money::minimum(Money $first, Money ...$rest)` | Return the smallest value | `Money` |
| `Money::maximum(Money $first, Money ...$rest)` | Return the largest value | `Money` |
| `Money::min(Money ...$amounts)` | Return the smallest value | `Money` |
| `Money::max(Money ...$amounts)` | Return the largest value | `Money` |
| `->getAmount()` | Get amount in smallest unit | `int` |
| `->getCurrency()` | Get Currency instance | `Currency` |
| `->add(Money $other)` | Add two money values | `Money` |
| `->subtract(Money $other)` | Subtract two money values | `Money` |
| `->multiply(int\|float $factor, ?RoundingMode $mode)` | Multiply by a factor | `Money` |
| `->divide(int\|float $divisor, ?RoundingMode $mode)` | Divide by a divisor | `Money` |
| `->percentage(int\|float $percent)` | Calculate a percentage | `Money` |
| `->convertTo(Currency $target, float $rate)` | Convert to another currency | `Money` |
| `->allocate(int[] $ratios)` | Split proportionally without rounding loss | `Money[]` |
| `->allocateEqual(int $parts)` | Split equally (remainder to first parts) | `Money[]` |
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

### RoundingMode

| Case | Value | Description |
|------|-------|-------------|
| `HALF_UP` | `half_up` | Round half away from zero (default) |
| `HALF_DOWN` | `half_down` | Round half toward zero |
| `HALF_EVEN` | `half_even` | Banker's rounding |
| `CEILING` | `ceiling` | Round toward positive infinity |
| `FLOOR` | `floor` | Round toward negative infinity |

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

## Support

If you find this project useful:

⭐ [Star the repo](https://github.com/philiprehberger/php-money)

🐛 [Report issues](https://github.com/philiprehberger/php-money/issues?q=is%3Aissue+is%3Aopen+label%3Abug)

💡 [Suggest features](https://github.com/philiprehberger/php-money/issues?q=is%3Aissue+is%3Aopen+label%3Aenhancement)

❤️ [Sponsor development](https://github.com/sponsors/philiprehberger)

🌐 [All Open Source Projects](https://philiprehberger.com/open-source-packages)

💻 [GitHub Profile](https://github.com/philiprehberger)

🔗 [LinkedIn Profile](https://www.linkedin.com/in/philiprehberger)

## License

[MIT](LICENSE)
