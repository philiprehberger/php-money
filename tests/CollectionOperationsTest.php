<?php

declare(strict_types=1);

namespace PhilipRehberger\Money\Tests;

use PhilipRehberger\Money\Exceptions\CurrencyMismatchException;
use PhilipRehberger\Money\Money;
use PHPUnit\Framework\TestCase;

class CollectionOperationsTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Sum
    // -------------------------------------------------------------------------

    public function test_sum_of_three_values(): void
    {
        $a = Money::USD(1000); // $10
        $b = Money::USD(2000); // $20
        $c = Money::USD(3000); // $30

        $result = Money::sum($a, $b, $c);

        $this->assertSame(6000, $result->getAmount());
        $this->assertSame('USD', $result->getCurrency()->getCode());
    }

    public function test_sum_single_value(): void
    {
        $a = Money::USD(1000);

        $this->assertSame(1000, Money::sum($a)->getAmount());
    }

    public function test_sum_currency_mismatch_throws(): void
    {
        $this->expectException(CurrencyMismatchException::class);

        Money::sum(Money::USD(1000), Money::EUR(2000));
    }

    // -------------------------------------------------------------------------
    // Avg
    // -------------------------------------------------------------------------

    public function test_avg_of_three_values(): void
    {
        $a = Money::USD(1000); // $10
        $b = Money::USD(2000); // $20
        $c = Money::USD(3000); // $30

        $result = Money::avg($a, $b, $c);

        $this->assertSame(2000, $result->getAmount());
    }

    public function test_avg_single_value(): void
    {
        $a = Money::USD(1000);

        $this->assertSame(1000, Money::avg($a)->getAmount());
    }

    public function test_avg_rounds_result(): void
    {
        // (1000 + 2000) / 2 = 1500 (exact)
        $this->assertSame(1500, Money::avg(Money::USD(1000), Money::USD(2000))->getAmount());

        // (1000 + 2000 + 3001) / 3 = 2000.333... → 2000
        $this->assertSame(2000, Money::avg(Money::USD(1000), Money::USD(2000), Money::USD(3001))->getAmount());
    }

    // -------------------------------------------------------------------------
    // Minimum / Maximum
    // -------------------------------------------------------------------------

    public function test_minimum_returns_smallest(): void
    {
        $a = Money::USD(500);
        $b = Money::USD(200);
        $c = Money::USD(800);

        $result = Money::minimum($a, $b, $c);

        $this->assertSame(200, $result->getAmount());
    }

    public function test_maximum_returns_largest(): void
    {
        $a = Money::USD(500);
        $b = Money::USD(200);
        $c = Money::USD(800);

        $result = Money::maximum($a, $b, $c);

        $this->assertSame(800, $result->getAmount());
    }

    public function test_minimum_single_value(): void
    {
        $a = Money::USD(500);

        $this->assertSame(500, Money::minimum($a)->getAmount());
    }

    public function test_maximum_single_value(): void
    {
        $a = Money::USD(500);

        $this->assertSame(500, Money::maximum($a)->getAmount());
    }

    public function test_minimum_currency_mismatch_throws(): void
    {
        $this->expectException(CurrencyMismatchException::class);

        Money::minimum(Money::USD(100), Money::EUR(200));
    }

    public function test_maximum_currency_mismatch_throws(): void
    {
        $this->expectException(CurrencyMismatchException::class);

        Money::maximum(Money::USD(100), Money::EUR(200));
    }
}
