<?php

declare(strict_types=1);

namespace PhilipRehberger\Money\Tests;

use PhilipRehberger\Money\Currency;
use PhilipRehberger\Money\Money;
use PHPUnit\Framework\TestCase;

class CurrencyConversionTest extends TestCase
{
    public function test_convert_usd_to_eur(): void
    {
        $usd = Money::USD(10000); // $100.00

        $eur = $usd->convertTo(Currency::EUR(), 0.85);

        $this->assertSame(8500, $eur->getAmount());
        $this->assertSame('EUR', $eur->getCurrency()->getCode());
    }

    public function test_convert_does_not_mutate_original(): void
    {
        $usd = Money::USD(10000);
        $eur = $usd->convertTo(Currency::EUR(), 0.85);

        $this->assertSame(10000, $usd->getAmount());
        $this->assertSame('USD', $usd->getCurrency()->getCode());
        $this->assertSame(8500, $eur->getAmount());
    }

    public function test_convert_with_rounding(): void
    {
        // 999 * 0.85 = 849.15 → rounds to 849
        $result = Money::USD(999)->convertTo(Currency::EUR(), 0.85);

        $this->assertSame(849, $result->getAmount());
    }

    public function test_convert_to_higher_value_currency(): void
    {
        // 1000 EUR * 1.18 = 1180 USD
        $eur = Money::EUR(1000);
        $usd = $eur->convertTo(Currency::USD(), 1.18);

        $this->assertSame(1180, $usd->getAmount());
        $this->assertSame('USD', $usd->getCurrency()->getCode());
    }

    public function test_convert_with_rate_of_one(): void
    {
        $usd = Money::USD(5000);
        $result = $usd->convertTo(Currency::CAD(), 1.0);

        $this->assertSame(5000, $result->getAmount());
        $this->assertSame('CAD', $result->getCurrency()->getCode());
    }
}
