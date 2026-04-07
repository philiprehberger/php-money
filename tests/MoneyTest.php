<?php

declare(strict_types=1);

namespace PhilipRehberger\Money\Tests;

use BadMethodCallException;
use PhilipRehberger\Money\Currency;
use PhilipRehberger\Money\Exceptions\CurrencyMismatchException;
use PhilipRehberger\Money\Exceptions\DivisionByZeroException;
use PhilipRehberger\Money\Exceptions\InvalidAmountException;
use PhilipRehberger\Money\Exceptions\ParseException;
use PhilipRehberger\Money\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Construction & static factories
    // -------------------------------------------------------------------------

    public function test_static_usd_factory(): void
    {
        $money = Money::USD(1999);

        $this->assertSame(1999, $money->getAmount());
        $this->assertSame('USD', $money->getCurrency()->getCode());
    }

    public function test_static_eur_factory(): void
    {
        $money = Money::EUR(1500);

        $this->assertSame(1500, $money->getAmount());
        $this->assertSame('EUR', $money->getCurrency()->getCode());
    }

    public function test_static_gbp_factory(): void
    {
        $money = Money::GBP(999);

        $this->assertSame(999, $money->getAmount());
        $this->assertSame('GBP', $money->getCurrency()->getCode());
    }

    public function test_of_factory(): void
    {
        $money = Money::of(500, 'CAD');

        $this->assertSame(500, $money->getAmount());
        $this->assertSame('CAD', $money->getCurrency()->getCode());
    }

    public function test_zero_factory_defaults_to_usd(): void
    {
        $money = Money::zero();

        $this->assertSame(0, $money->getAmount());
        $this->assertSame('USD', $money->getCurrency()->getCode());
        $this->assertTrue($money->isZero());
    }

    public function test_zero_factory_with_currency(): void
    {
        $money = Money::zero('JPY');

        $this->assertSame(0, $money->getAmount());
        $this->assertSame('JPY', $money->getCurrency()->getCode());
    }

    // -------------------------------------------------------------------------
    // Immutability
    // -------------------------------------------------------------------------

    public function test_arithmetic_does_not_mutate_original(): void
    {
        $original = Money::USD(1000);
        $result = $original->add(Money::USD(500));

        $this->assertSame(1000, $original->getAmount());
        $this->assertSame(1500, $result->getAmount());
        $this->assertNotSame($original, $result);
    }

    public function test_multiply_does_not_mutate_original(): void
    {
        $original = Money::USD(1000);
        $result = $original->multiply(2);

        $this->assertSame(1000, $original->getAmount());
        $this->assertSame(2000, $result->getAmount());
    }

    // -------------------------------------------------------------------------
    // Arithmetic
    // -------------------------------------------------------------------------

    public function test_add(): void
    {
        $a = Money::USD(1000);
        $b = Money::USD(500);

        $this->assertSame(1500, $a->add($b)->getAmount());
    }

    public function test_subtract(): void
    {
        $a = Money::USD(1000);
        $b = Money::USD(300);

        $this->assertSame(700, $a->subtract($b)->getAmount());
    }

    public function test_subtract_results_in_negative(): void
    {
        $a = Money::USD(100);
        $b = Money::USD(500);
        $result = $a->subtract($b);

        $this->assertSame(-400, $result->getAmount());
        $this->assertTrue($result->isNegative());
    }

    public function test_multiply_by_integer(): void
    {
        $money = Money::USD(1000);

        $this->assertSame(3000, $money->multiply(3)->getAmount());
    }

    public function test_multiply_by_float_rounds_half_up(): void
    {
        // 1000 * 1.5 = 1500 (exact)
        $this->assertSame(1500, Money::USD(1000)->multiply(1.5)->getAmount());

        // 333 * 1.5 = 499.5 → rounds to 500
        $this->assertSame(500, Money::USD(333)->multiply(1.5)->getAmount());

        // 1 * 0.5 = 0.5 → rounds to 1
        $this->assertSame(1, Money::USD(1)->multiply(0.5)->getAmount());
    }

    public function test_divide(): void
    {
        $this->assertSame(500, Money::USD(1000)->divide(2)->getAmount());
    }

    public function test_divide_rounds_half_up(): void
    {
        // 1000 / 3 = 333.333... → 333
        $this->assertSame(333, Money::USD(1000)->divide(3)->getAmount());

        // 1001 / 2 = 500.5 → 501
        $this->assertSame(501, Money::USD(1001)->divide(2)->getAmount());
    }

    public function test_divide_by_float(): void
    {
        // 1000 / 0.1 = 10000
        $this->assertSame(10000, Money::USD(1000)->divide(0.1)->getAmount());
    }

    public function test_divide_by_zero_throws(): void
    {
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Division by zero');

        Money::USD(1000)->divide(0);
    }

    public function test_percentage(): void
    {
        // 10% of $10.00 = $1.00 (100 cents)
        $this->assertSame(100, Money::USD(1000)->percentage(10)->getAmount());
    }

    public function test_percentage_fractional(): void
    {
        // 7.5% of $100.00 = $7.50 (750 cents)
        $this->assertSame(750, Money::USD(10000)->percentage(7.5)->getAmount());
    }

    public function test_percentage_100(): void
    {
        $money = Money::USD(1234);
        $this->assertSame(1234, $money->percentage(100)->getAmount());
    }

    // -------------------------------------------------------------------------
    // Comparison
    // -------------------------------------------------------------------------

    public function test_equals_same_amount(): void
    {
        $this->assertTrue(Money::USD(1000)->equals(Money::USD(1000)));
    }

    public function test_equals_different_amount(): void
    {
        $this->assertFalse(Money::USD(1000)->equals(Money::USD(999)));
    }

    public function test_greater_than(): void
    {
        $this->assertTrue(Money::USD(1001)->greaterThan(Money::USD(1000)));
        $this->assertFalse(Money::USD(1000)->greaterThan(Money::USD(1000)));
        $this->assertFalse(Money::USD(999)->greaterThan(Money::USD(1000)));
    }

    public function test_less_than(): void
    {
        $this->assertTrue(Money::USD(999)->lessThan(Money::USD(1000)));
        $this->assertFalse(Money::USD(1000)->lessThan(Money::USD(1000)));
        $this->assertFalse(Money::USD(1001)->lessThan(Money::USD(1000)));
    }

    public function test_greater_than_or_equal(): void
    {
        $this->assertTrue(Money::USD(1000)->greaterThanOrEqual(Money::USD(1000)));
        $this->assertTrue(Money::USD(1001)->greaterThanOrEqual(Money::USD(1000)));
        $this->assertFalse(Money::USD(999)->greaterThanOrEqual(Money::USD(1000)));
    }

    public function test_less_than_or_equal(): void
    {
        $this->assertTrue(Money::USD(1000)->lessThanOrEqual(Money::USD(1000)));
        $this->assertTrue(Money::USD(999)->lessThanOrEqual(Money::USD(1000)));
        $this->assertFalse(Money::USD(1001)->lessThanOrEqual(Money::USD(1000)));
    }

    public function test_is_zero(): void
    {
        $this->assertTrue(Money::USD(0)->isZero());
        $this->assertFalse(Money::USD(1)->isZero());
        $this->assertFalse(Money::USD(-1)->isZero());
    }

    public function test_is_positive(): void
    {
        $this->assertTrue(Money::USD(1)->isPositive());
        $this->assertFalse(Money::USD(0)->isPositive());
        $this->assertFalse(Money::USD(-1)->isPositive());
    }

    public function test_is_negative(): void
    {
        $this->assertTrue(Money::USD(-1)->isNegative());
        $this->assertFalse(Money::USD(0)->isNegative());
        $this->assertFalse(Money::USD(1)->isNegative());
    }

    // -------------------------------------------------------------------------
    // Min / Max
    // -------------------------------------------------------------------------

    public function test_min_returns_smallest(): void
    {
        $a = Money::USD(500);
        $b = Money::USD(200);
        $c = Money::USD(800);

        $result = Money::min($a, $b, $c);

        $this->assertSame(200, $result->getAmount());
    }

    public function test_max_returns_largest(): void
    {
        $a = Money::USD(500);
        $b = Money::USD(200);
        $c = Money::USD(800);

        $result = Money::max($a, $b, $c);

        $this->assertSame(800, $result->getAmount());
    }

    public function test_min_single_value(): void
    {
        $a = Money::USD(500);

        $this->assertSame(500, Money::min($a)->getAmount());
    }

    public function test_max_single_value(): void
    {
        $a = Money::USD(500);

        $this->assertSame(500, Money::max($a)->getAmount());
    }

    public function test_min_currency_mismatch_throws(): void
    {
        $this->expectException(CurrencyMismatchException::class);

        Money::min(Money::USD(100), Money::EUR(200));
    }

    public function test_max_currency_mismatch_throws(): void
    {
        $this->expectException(CurrencyMismatchException::class);

        Money::max(Money::USD(100), Money::EUR(200));
    }

    public function test_min_no_arguments_throws(): void
    {
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('at least one');

        Money::min();
    }

    public function test_max_no_arguments_throws(): void
    {
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('at least one');

        Money::max();
    }

    // -------------------------------------------------------------------------
    // Currency mismatch
    // -------------------------------------------------------------------------

    public function test_add_currency_mismatch_throws(): void
    {
        $this->expectException(CurrencyMismatchException::class);
        $this->expectExceptionMessage('Currency mismatch');

        Money::USD(1000)->add(Money::EUR(500));
    }

    public function test_subtract_currency_mismatch_throws(): void
    {
        $this->expectException(CurrencyMismatchException::class);

        Money::USD(1000)->subtract(Money::EUR(500));
    }

    public function test_equals_currency_mismatch_throws(): void
    {
        $this->expectException(CurrencyMismatchException::class);

        Money::USD(1000)->equals(Money::EUR(1000));
    }

    public function test_compare_currency_mismatch_throws(): void
    {
        $this->expectException(CurrencyMismatchException::class);

        Money::USD(1000)->greaterThan(Money::EUR(500));
    }

    // -------------------------------------------------------------------------
    // Allocation
    // -------------------------------------------------------------------------

    public function test_allocate_even_split(): void
    {
        $parts = Money::USD(1000)->allocate([1, 1]);

        $this->assertCount(2, $parts);
        $this->assertSame(500, $parts[0]->getAmount());
        $this->assertSame(500, $parts[1]->getAmount());
    }

    public function test_allocate_distributes_remainder_correctly(): void
    {
        // $10.00 (1000 cents) split 3 ways
        // Exact: 333.33... each → floor: 333, remainder: 1
        // First party gets the extra cent
        $parts = Money::USD(1000)->allocate([1, 1, 1]);

        $this->assertCount(3, $parts);
        $this->assertSame(334, $parts[0]->getAmount());
        $this->assertSame(333, $parts[1]->getAmount());
        $this->assertSame(333, $parts[2]->getAmount());

        // Total must equal original
        $total = array_sum(array_map(fn ($m) => $m->getAmount(), $parts));
        $this->assertSame(1000, $total);
    }

    public function test_allocate_uneven_ratios(): void
    {
        // $10.00 split 70/30
        $parts = Money::USD(1000)->allocate([70, 30]);

        $this->assertCount(2, $parts);
        $this->assertSame(700, $parts[0]->getAmount());
        $this->assertSame(300, $parts[1]->getAmount());
    }

    public function test_allocate_preserves_total(): void
    {
        $original = Money::USD(99);
        $parts = $original->allocate([1, 1, 1]);

        $total = array_sum(array_map(fn ($m) => $m->getAmount(), $parts));
        $this->assertSame(99, $total);
    }

    public function test_allocate_single_ratio(): void
    {
        $parts = Money::USD(1000)->allocate([1]);

        $this->assertCount(1, $parts);
        $this->assertSame(1000, $parts[0]->getAmount());
    }

    public function test_allocate_empty_ratios_throws(): void
    {
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('empty');

        Money::USD(1000)->allocate([]);
    }

    public function test_allocate_negative_ratio_throws(): void
    {
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('non-negative');

        Money::USD(1000)->allocate([1, -1]);
    }

    public function test_allocate_all_zero_ratios_throws(): void
    {
        $this->expectException(InvalidAmountException::class);

        Money::USD(1000)->allocate([0, 0]);
    }

    // -------------------------------------------------------------------------
    // allocateEqual
    // -------------------------------------------------------------------------

    public function test_allocate_equal_even_division(): void
    {
        $parts = Money::USD(1000)->allocateEqual(2);

        $this->assertCount(2, $parts);
        $this->assertSame(500, $parts[0]->getAmount());
        $this->assertSame(500, $parts[1]->getAmount());
    }

    public function test_allocate_equal_uneven_division(): void
    {
        $parts = Money::USD(1000)->allocateEqual(3);

        $this->assertCount(3, $parts);
        $this->assertSame(334, $parts[0]->getAmount());
        $this->assertSame(333, $parts[1]->getAmount());
        $this->assertSame(333, $parts[2]->getAmount());

        $total = array_sum(array_map(fn ($m) => $m->getAmount(), $parts));
        $this->assertSame(1000, $total);
    }

    public function test_allocate_equal_single_part(): void
    {
        $parts = Money::USD(1000)->allocateEqual(1);

        $this->assertCount(1, $parts);
        $this->assertSame(1000, $parts[0]->getAmount());
    }

    public function test_allocate_equal_zero_parts_throws(): void
    {
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('at least 1');

        Money::USD(1000)->allocateEqual(0);
    }

    public function test_allocate_equal_negative_parts_throws(): void
    {
        $this->expectException(InvalidAmountException::class);

        Money::USD(1000)->allocateEqual(-1);
    }

    // -------------------------------------------------------------------------
    // Parse
    // -------------------------------------------------------------------------

    public function test_parse_dollar_string(): void
    {
        $money = Money::parse('$19.99', 'USD');

        $this->assertSame(1999, $money->getAmount());
        $this->assertSame('USD', $money->getCurrency()->getCode());
    }

    public function test_parse_euro_string(): void
    {
        $money = Money::parse('€15.00', 'EUR');

        $this->assertSame(1500, $money->getAmount());
    }

    public function test_parse_plain_numeric_string(): void
    {
        $money = Money::parse('9.99', 'USD');

        $this->assertSame(999, $money->getAmount());
    }

    public function test_parse_string_with_commas(): void
    {
        $money = Money::parse('$1,299.99', 'USD');

        $this->assertSame(129999, $money->getAmount());
    }

    public function test_parse_zero_decimal_currency(): void
    {
        // JPY has 0 decimal places
        $money = Money::parse('¥1500', 'JPY');

        $this->assertSame(1500, $money->getAmount());
    }

    public function test_parse_invalid_string_throws(): void
    {
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Cannot parse');

        Money::parse('not-a-number', 'USD');
    }

    // -------------------------------------------------------------------------
    // Formatting
    // -------------------------------------------------------------------------

    public function test_format_usd_en_us(): void
    {
        $formatted = Money::USD(1999)->format('en_US');

        $this->assertStringContainsString('19.99', $formatted);
        $this->assertStringContainsString('$', $formatted);
    }

    public function test_format_eur_de_de(): void
    {
        $formatted = Money::EUR(1500)->format('de_DE');

        $this->assertStringContainsString('15', $formatted);
    }

    public function test_format_jpy_no_decimals(): void
    {
        $formatted = Money::JPY(1500)->format('ja_JP');

        // JPY has 0 decimal places
        $this->assertStringContainsString('1,500', $formatted);
    }

    // -------------------------------------------------------------------------
    // JSON serialization
    // -------------------------------------------------------------------------

    public function test_json_serialize(): void
    {
        $money = Money::USD(1999);
        $json = json_encode($money);

        $this->assertSame('{"amount":1999,"currency":"USD"}', $json);
    }

    public function test_json_serialize_eur(): void
    {
        $money = Money::EUR(500);
        $decoded = json_decode(json_encode($money), true);

        $this->assertSame(['amount' => 500, 'currency' => 'EUR'], $decoded);
    }

    // -------------------------------------------------------------------------
    // toArray
    // -------------------------------------------------------------------------

    public function test_to_array(): void
    {
        $money = Money::USD(1234);

        $this->assertSame(['amount' => 1234, 'currency' => 'USD'], $money->toArray());
    }

    // -------------------------------------------------------------------------
    // Stringable
    // -------------------------------------------------------------------------

    public function test_to_string(): void
    {
        $money = Money::USD(1000);
        $str = (string) $money;

        $this->assertStringContainsString('10', $str);
    }

    // -------------------------------------------------------------------------
    // Edge cases
    // -------------------------------------------------------------------------

    public function test_negative_amounts_are_allowed(): void
    {
        $money = Money::USD(-500);

        $this->assertSame(-500, $money->getAmount());
        $this->assertTrue($money->isNegative());
    }

    public function test_very_large_amounts(): void
    {
        $money = Money::USD(1_000_000_00); // $1,000,000.00

        $this->assertSame(100_000_000, $money->getAmount());
    }

    public function test_get_currency_returns_currency_instance(): void
    {
        $money = Money::USD(100);

        $this->assertInstanceOf(Currency::class, $money->getCurrency());
        $this->assertSame('USD', $money->getCurrency()->getCode());
    }

    public function test_multiply_by_zero(): void
    {
        $money = Money::USD(1000)->multiply(0);

        $this->assertSame(0, $money->getAmount());
        $this->assertTrue($money->isZero());
    }

    public function test_divide_by_zero_float_throws_exception(): void
    {
        $this->expectException(InvalidAmountException::class);
        Money::USD(1000)->divide(0.0);
    }

    public function test_percentage_with_negative_value(): void
    {
        $result = Money::USD(10000)->percentage(-10);
        $this->assertSame(-1000, $result->getAmount());
    }

    // -------------------------------------------------------------------------
    // DivisionByZeroException / ParseException
    // -------------------------------------------------------------------------

    public function test_divide_by_zero_throws_division_by_zero_exception(): void
    {
        $this->expectException(DivisionByZeroException::class);
        Money::USD(1000)->divide(0);
    }

    public function test_division_by_zero_exception_extends_invalid_amount_exception(): void
    {
        try {
            Money::USD(1000)->divide(0);
            $this->fail('Expected DivisionByZeroException');
        } catch (DivisionByZeroException $e) {
            $this->assertInstanceOf(InvalidAmountException::class, $e);
        }
    }

    public function test_parse_invalid_string_throws_parse_exception(): void
    {
        $this->expectException(ParseException::class);
        Money::parse('not-a-number', 'USD');
    }

    public function test_parse_exception_extends_invalid_amount_exception(): void
    {
        try {
            Money::parse('garbage', 'USD');
            $this->fail('Expected ParseException');
        } catch (ParseException $e) {
            $this->assertInstanceOf(InvalidAmountException::class, $e);
        }
    }

    // -------------------------------------------------------------------------
    // ArrayAccess
    // -------------------------------------------------------------------------

    public function test_array_access_read_keys(): void
    {
        $money = Money::USD(1999);

        $this->assertTrue(isset($money['amount']));
        $this->assertTrue(isset($money['currency']));
        $this->assertTrue(isset($money['minor']));
        $this->assertFalse(isset($money['unknown']));

        $this->assertSame('1999', $money['amount']);
        $this->assertSame('USD', $money['currency']);
        $this->assertSame(1999, $money['minor']);
    }

    public function test_array_access_set_throws(): void
    {
        $this->expectException(BadMethodCallException::class);
        $money = Money::USD(100);
        $money['amount'] = 200;
    }

    public function test_array_access_unset_throws(): void
    {
        $this->expectException(BadMethodCallException::class);
        $money = Money::USD(100);
        unset($money['amount']);
    }
}
