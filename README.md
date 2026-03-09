# php-money

[![Tests](https://github.com/philiprehberger/php-money/actions/workflows/tests.yml/badge.svg)](https://github.com/philiprehberger/php-money/actions/workflows/tests.yml)
[![PHPStan Level 8](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg)](https://phpstan.org/)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Latest Version](https://img.shields.io/packagist/v/philiprehberger/php-money.svg)](https://packagist.org/packages/philiprehberger/php-money)

An immutable Money value object for PHP 8.2+ with currency support, locale-aware formatting, arithmetic, proportional allocation, and an optional Laravel Eloquent cast.

---

## Features

- Immutable — every operation returns a new `Money` instance
- Integer-based storage in smallest currency unit (cents, pence, etc.) — no floating-point rounding surprises
- 25 built-in ISO 4217 currencies via `Currency` registry
- Locale-aware formatting via PHP's `ext-intl` `NumberFormatter`
- Proportional allocation without losing a single cent (largest-remainder method)
- Parse formatted strings like `"$19.99"` back to `Money`
- Full arithmetic: add, subtract, multiply, divide, percentage
- Clear exception types for currency mismatches and invalid operations
- Laravel Eloquent cast — stores as JSON, zero configuration required
- PHPStan level 8 clean, PSR-12 code style

---

## Requirements

- PHP ^8.2
- `ext-intl` (for `format()`)
- Laravel ^11.0|^12.0 (optional, only for `MoneyCast`)

---

## Installation

```bash
composer require philiprehberger/php-money
```

### Laravel

The package auto-discovers `MoneyServiceProvider`. To publish the optional config file:

```bash
php artisan vendor:publish --tag=money-config
```

This creates `config/money.php` where you can set the default currency and locale.

---

## Basic Usage

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

### Reading values

```php
$money = Money::USD(1999);

$money->getAmount();            // 1999 (int — cents)
$money->getCurrency();          // Currency instance
$money->getCurrency()->getCode();  // "USD"
```

---

## Arithmetic

All arithmetic methods return new `Money` instances and leave the original unchanged.

```php
$subtotal = Money::USD(1000);  // $10.00
$tax      = Money::USD(80);    //  $0.80
$discount = Money::USD(150);   //  $1.50

$total = $subtotal
    ->add($tax)
    ->subtract($discount);

$total->getAmount(); // 930 (= $9.30)
```

### Multiply & Divide

```php
$price = Money::USD(999);          // $9.99

$tripled = $price->multiply(3);    // $29.97 (2997 cents)
$halved  = $price->divide(2);      // $5.00 (rounded half-up: 500)

// Float factors
$withTax = $price->multiply(1.1);  // $10.99 (rounded half-up)
```

### Percentage

```php
$order = Money::USD(10000);  // $100.00

$tenPercent   = $order->percentage(10);    // $10.00
$vatUk        = $order->percentage(20);    // $20.00
$salesTax     = $order->percentage(8.875); // $8.88
```

---

## Comparison

```php
$a = Money::USD(1000);
$b = Money::USD(2000);

$a->equals($b);               // false
$a->greaterThan($b);          // false
$a->lessThan($b);             // true
$a->greaterThanOrEqual($b);   // false
$a->lessThanOrEqual($b);      // true

$a->isZero();      // false
$a->isPositive();  // true
$a->isNegative();  // false

Money::USD(0)->isZero(); // true
```

Comparing money values of different currencies throws `CurrencyMismatchException`.

---

## Allocation

Split a money value proportionally without losing a single cent. Remainders are distributed one unit at a time to the parties with the largest fractional shares.

```php
// Split $10.00 three ways
$parts = Money::USD(1000)->allocate([1, 1, 1]);
// [334, 333, 333] — totals exactly 1000

// Split $100.00 between partners at 60/30/10
$shares = Money::USD(10000)->allocate([60, 30, 10]);
// [6000, 3000, 1000]

// Uneven split — no cent is lost
$invoice = Money::USD(99)->allocate([1, 1, 1]);
// [33, 33, 33] — totals 99
```

---

## Parsing

`Money::parse()` strips currency symbols and thousands separators, then converts the decimal string to the smallest unit.

```php
Money::parse('$19.99',     'USD');  // 1999 cents
Money::parse('€15.00',    'EUR');  // 1500 cents
Money::parse('£9.99',     'GBP');  //  999 pence
Money::parse('¥1500',     'JPY');  // 1500 yen (0 decimal places)
Money::parse('1,299.99',  'USD');  // 129999 cents
Money::parse('9.99',      'USD');  //  999 cents
```

Invalid strings throw `InvalidAmountException`.

---

## Formatting

`format()` uses PHP's `NumberFormatter` (from `ext-intl`) for full locale-aware output.

```php
$price = Money::USD(1234567);  // $12,345.67

$price->format('en_US');  // "$12,345.67"
$price->format('de_DE');  // "12.345,67 $"
$price->format('fr_FR');  // "12 345,67 $US"

Money::EUR(1500)->format('de_DE');  // "15,00 €"
Money::JPY(1500)->format('ja_JP');  // "￥1,500"
```

The default locale is `'en_US'` when none is provided.

---

## Currency

```php
use PhilipRehberger\Money\Currency;

// Built-in registry
$usd = Currency::USD();
$eur = Currency::fromCode('EUR');

$usd->getCode();           // "USD"
$usd->getDecimalPlaces();  // 2
$usd->getSymbol();         // "$"
(string) $usd;             // "USD"

// Currencies with 0 decimal places
Currency::JPY()->getDecimalPlaces();  // 0
Currency::KRW()->getDecimalPlaces();  // 0

// Custom currencies (not in registry)
$bitcoin = new Currency('XBT', 8, '₿');
```

### Built-in currencies

| Code | Name                | Decimals | Symbol |
|------|---------------------|----------|--------|
| AED  | UAE Dirham          | 2        | د.إ   |
| AUD  | Australian Dollar   | 2        | A$     |
| BRL  | Brazilian Real      | 2        | R$     |
| CAD  | Canadian Dollar     | 2        | CA$    |
| CHF  | Swiss Franc         | 2        | CHF    |
| CNY  | Chinese Yuan        | 2        | ¥      |
| CZK  | Czech Koruna        | 2        | Kč     |
| DKK  | Danish Krone        | 2        | kr     |
| EUR  | Euro                | 2        | €      |
| GBP  | British Pound       | 2        | £      |
| HKD  | Hong Kong Dollar    | 2        | HK$    |
| HUF  | Hungarian Forint    | 2        | Ft     |
| INR  | Indian Rupee        | 2        | ₹      |
| JPY  | Japanese Yen        | 0        | ¥      |
| KRW  | South Korean Won   | 0        | ₩      |
| MXN  | Mexican Peso        | 2        | MX$    |
| NOK  | Norwegian Krone     | 2        | kr     |
| NZD  | New Zealand Dollar  | 2        | NZ$    |
| PLN  | Polish Zloty        | 2        | zł     |
| SEK  | Swedish Krona       | 2        | kr     |
| SGD  | Singapore Dollar    | 2        | S$     |
| THB  | Thai Baht           | 2        | ฿      |
| TRY  | Turkish Lira        | 2        | ₺      |
| USD  | US Dollar           | 2        | $      |
| ZAR  | South African Rand  | 2        | R      |

---

## Serialisation

`Money` implements `JsonSerializable` and `Stringable`.

```php
$money = Money::USD(1999);

// JSON
json_encode($money);
// {"amount":1999,"currency":"USD"}

// Array
$money->toArray();
// ['amount' => 1999, 'currency' => 'USD']

// String (uses format() with default locale)
(string) $money;  // "$19.99"
echo $money;      // "$19.99"
```

---

## Laravel Eloquent Cast

Add `MoneyCast` to your model to store money as JSON in a single column.

### Migration

```php
Schema::table('products', function (Blueprint $table) {
    $table->text('price')->nullable();
});
```

### Model

```php
use PhilipRehberger\Money\Laravel\MoneyCast;

class Product extends Model
{
    protected $casts = [
        'price' => MoneyCast::class,
    ];
}
```

With a default currency (for legacy integer columns):

```php
protected $casts = [
    'price' => MoneyCast::class . ':EUR',
];
```

### Usage

```php
// Save
$product = new Product();
$product->price = Money::USD(2999);
$product->save();
// Stored as: {"amount":2999,"currency":"USD"}

// Retrieve
$product = Product::find(1);
$product->price;                        // Money instance
$product->price->getAmount();           // 2999
$product->price->format('en_US');       // "$29.99"

// Use Money methods directly
$withTax = $product->price->percentage(10)->add($product->price);
```

---

## Exception Reference

| Exception                  | When thrown                                                        |
|----------------------------|--------------------------------------------------------------------|
| `CurrencyMismatchException` | Arithmetic or comparison between different currencies             |
| `InvalidAmountException`    | Division by zero, unparseable string, empty/negative ratios       |
| `InvalidArgumentException`  | Unknown currency code, empty currency code, invalid cast input    |

```php
use PhilipRehberger\Money\Exceptions\CurrencyMismatchException;
use PhilipRehberger\Money\Exceptions\InvalidAmountException;

try {
    Money::USD(100)->add(Money::EUR(100));
} catch (CurrencyMismatchException $e) {
    // "Currency mismatch: expected USD but got EUR."
}

try {
    Money::USD(100)->divide(0);
} catch (InvalidAmountException $e) {
    // "Division by zero is not allowed."
}
```

---

## Running Tests

```bash
composer install
composer test
```

Static analysis:

```bash
composer phpstan
```

Code style check:

```bash
composer pint
```

Run everything at once:

```bash
composer check
```

---

## License

MIT — see [LICENSE](LICENSE).
