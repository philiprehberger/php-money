<?php

declare(strict_types=1);

namespace PhilipRehberger\Money\Tests;

use InvalidArgumentException;
use PhilipRehberger\Money\Currency;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Registry & fromCode
    // -------------------------------------------------------------------------

    public function test_from_code_usd(): void
    {
        $currency = Currency::fromCode('USD');

        $this->assertSame('USD', $currency->getCode());
        $this->assertSame(2, $currency->getDecimalPlaces());
        $this->assertSame('$', $currency->getSymbol());
    }

    public function test_from_code_eur(): void
    {
        $currency = Currency::fromCode('EUR');

        $this->assertSame('EUR', $currency->getCode());
        $this->assertSame(2, $currency->getDecimalPlaces());
        $this->assertSame('€', $currency->getSymbol());
    }

    public function test_from_code_gbp(): void
    {
        $currency = Currency::fromCode('GBP');

        $this->assertSame('GBP', $currency->getCode());
        $this->assertSame('£', $currency->getSymbol());
    }

    public function test_from_code_jpy_has_zero_decimal_places(): void
    {
        $currency = Currency::fromCode('JPY');

        $this->assertSame('JPY', $currency->getCode());
        $this->assertSame(0, $currency->getDecimalPlaces());
    }

    public function test_from_code_krw_has_zero_decimal_places(): void
    {
        $currency = Currency::fromCode('KRW');

        $this->assertSame(0, $currency->getDecimalPlaces());
    }

    public function test_from_code_is_case_insensitive(): void
    {
        $lower = Currency::fromCode('usd');
        $upper = Currency::fromCode('USD');

        $this->assertSame('USD', $lower->getCode());
        $this->assertTrue($lower->equals($upper));
    }

    public function test_from_code_unknown_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown currency code');

        Currency::fromCode('XYZ');
    }

    // -------------------------------------------------------------------------
    // Static factory helpers
    // -------------------------------------------------------------------------

    public function test_static_usd(): void
    {
        $currency = Currency::USD();

        $this->assertSame('USD', $currency->getCode());
        $this->assertSame(2, $currency->getDecimalPlaces());
    }

    public function test_static_eur(): void
    {
        $currency = Currency::EUR();

        $this->assertSame('EUR', $currency->getCode());
    }

    public function test_static_gbp(): void
    {
        $currency = Currency::GBP();

        $this->assertSame('GBP', $currency->getCode());
    }

    public function test_static_jpy(): void
    {
        $currency = Currency::JPY();

        $this->assertSame('JPY', $currency->getCode());
        $this->assertSame(0, $currency->getDecimalPlaces());
    }

    public function test_static_cad(): void
    {
        $this->assertSame('CAD', Currency::CAD()->getCode());
    }

    public function test_static_aud(): void
    {
        $this->assertSame('AUD', Currency::AUD()->getCode());
    }

    public function test_static_chf(): void
    {
        $this->assertSame('CHF', Currency::CHF()->getCode());
    }

    public function test_static_cny(): void
    {
        $this->assertSame('CNY', Currency::CNY()->getCode());
    }

    public function test_static_inr(): void
    {
        $this->assertSame('INR', Currency::INR()->getCode());
    }

    public function test_static_brl(): void
    {
        $this->assertSame('BRL', Currency::BRL()->getCode());
    }

    public function test_static_mxn(): void
    {
        $this->assertSame('MXN', Currency::MXN()->getCode());
    }

    public function test_static_aed(): void
    {
        $this->assertSame('AED', Currency::AED()->getCode());
    }

    public function test_static_sgd(): void
    {
        $this->assertSame('SGD', Currency::SGD()->getCode());
    }

    public function test_static_hkd(): void
    {
        $this->assertSame('HKD', Currency::HKD()->getCode());
    }

    // -------------------------------------------------------------------------
    // Equality
    // -------------------------------------------------------------------------

    public function test_equals_same_code(): void
    {
        $a = Currency::USD();
        $b = Currency::fromCode('USD');

        $this->assertTrue($a->equals($b));
    }

    public function test_not_equals_different_code(): void
    {
        $a = Currency::USD();
        $b = Currency::EUR();

        $this->assertFalse($a->equals($b));
    }

    // -------------------------------------------------------------------------
    // Custom currencies
    // -------------------------------------------------------------------------

    public function test_custom_currency_via_constructor(): void
    {
        $custom = new Currency('XBT', 8, '₿');

        $this->assertSame('XBT', $custom->getCode());
        $this->assertSame(8, $custom->getDecimalPlaces());
        $this->assertSame('₿', $custom->getSymbol());
    }

    public function test_empty_code_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Currency code must not be empty');

        new Currency('');
    }

    // -------------------------------------------------------------------------
    // Stringable
    // -------------------------------------------------------------------------

    public function test_to_string_returns_code(): void
    {
        $currency = Currency::USD();

        $this->assertSame('USD', (string) $currency);
    }

    // -------------------------------------------------------------------------
    // Additional currency factory tests
    // -------------------------------------------------------------------------

    public function test_czk_factory(): void
    {
        $currency = Currency::CZK();
        $this->assertSame('CZK', $currency->getCode());
        $this->assertSame(2, $currency->getDecimalPlaces());
    }

    public function test_dkk_factory(): void
    {
        $currency = Currency::DKK();
        $this->assertSame('DKK', $currency->getCode());
        $this->assertSame(2, $currency->getDecimalPlaces());
    }

    public function test_huf_factory(): void
    {
        $currency = Currency::HUF();
        $this->assertSame('HUF', $currency->getCode());
        $this->assertSame(2, $currency->getDecimalPlaces());
    }

    public function test_krw_factory(): void
    {
        $currency = Currency::KRW();
        $this->assertSame('KRW', $currency->getCode());
        $this->assertSame(0, $currency->getDecimalPlaces());
    }

    public function test_nok_factory(): void
    {
        $currency = Currency::NOK();
        $this->assertSame('NOK', $currency->getCode());
        $this->assertSame(2, $currency->getDecimalPlaces());
    }

    public function test_nzd_factory(): void
    {
        $currency = Currency::NZD();
        $this->assertSame('NZD', $currency->getCode());
        $this->assertSame(2, $currency->getDecimalPlaces());
    }

    public function test_pln_factory(): void
    {
        $currency = Currency::PLN();
        $this->assertSame('PLN', $currency->getCode());
        $this->assertSame(2, $currency->getDecimalPlaces());
    }

    public function test_sek_factory(): void
    {
        $currency = Currency::SEK();
        $this->assertSame('SEK', $currency->getCode());
        $this->assertSame(2, $currency->getDecimalPlaces());
    }

    public function test_thb_factory(): void
    {
        $currency = Currency::THB();
        $this->assertSame('THB', $currency->getCode());
        $this->assertSame(2, $currency->getDecimalPlaces());
    }

    public function test_try_factory(): void
    {
        $currency = Currency::TRY();
        $this->assertSame('TRY', $currency->getCode());
        $this->assertSame(2, $currency->getDecimalPlaces());
    }

    public function test_zar_factory(): void
    {
        $currency = Currency::ZAR();
        $this->assertSame('ZAR', $currency->getCode());
        $this->assertSame(2, $currency->getDecimalPlaces());
    }
}
