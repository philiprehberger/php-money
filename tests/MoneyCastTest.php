<?php

declare(strict_types=1);

namespace PhilipRehberger\Money\Tests;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Orchestra\Testbench\TestCase;
use PhilipRehberger\Money\Laravel\MoneyCast;
use PhilipRehberger\Money\Money;

/**
 * Tests for MoneyCast using Orchestra Testbench.
 */
class MoneyCastTest extends TestCase
{
    private MoneyCast $cast;

    private Model $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cast = new MoneyCast('USD');

        // Anonymous model stub — we only need it as a parameter placeholder
        $this->model = new class extends Model {};
    }

    // -------------------------------------------------------------------------
    // get() — database → PHP
    // -------------------------------------------------------------------------

    public function test_get_null_returns_null(): void
    {
        $result = $this->cast->get($this->model, 'price', null, []);

        $this->assertNull($result);
    }

    public function test_get_json_string_returns_money(): void
    {
        $result = $this->cast->get($this->model, 'price', '{"amount":1999,"currency":"USD"}', []);

        $this->assertInstanceOf(Money::class, $result);
        $this->assertSame(1999, $result->getAmount());
        $this->assertSame('USD', $result->getCurrency()->getCode());
    }

    public function test_get_array_returns_money(): void
    {
        $result = $this->cast->get($this->model, 'price', ['amount' => 1500, 'currency' => 'EUR'], []);

        $this->assertInstanceOf(Money::class, $result);
        $this->assertSame(1500, $result->getAmount());
        $this->assertSame('EUR', $result->getCurrency()->getCode());
    }

    public function test_get_integer_uses_default_currency(): void
    {
        $result = $this->cast->get($this->model, 'price', 999, []);

        $this->assertInstanceOf(Money::class, $result);
        $this->assertSame(999, $result->getAmount());
        $this->assertSame('USD', $result->getCurrency()->getCode());
    }

    public function test_get_with_custom_default_currency(): void
    {
        $cast = new MoneyCast('EUR');
        $result = $cast->get($this->model, 'price', 800, []);

        $this->assertSame('EUR', $result->getCurrency()->getCode());
    }

    public function test_get_invalid_json_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected JSON object');

        $this->cast->get($this->model, 'price', 'not-json', []);
    }

    public function test_get_array_missing_keys_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"amount" and "currency" keys');

        $this->cast->get($this->model, 'price', ['amount' => 1000], []);
    }

    // -------------------------------------------------------------------------
    // set() — PHP → database
    // -------------------------------------------------------------------------

    public function test_set_null_returns_null(): void
    {
        $result = $this->cast->set($this->model, 'price', null, []);

        $this->assertNull($result);
    }

    public function test_set_money_instance_returns_json(): void
    {
        $money = Money::USD(1999);
        $result = $this->cast->set($this->model, 'price', $money, []);

        $this->assertIsString($result);

        $decoded = json_decode($result, true);
        $this->assertSame(['amount' => 1999, 'currency' => 'USD'], $decoded);
    }

    public function test_set_money_eur(): void
    {
        $money = Money::EUR(750);
        $result = $this->cast->set($this->model, 'price', $money, []);

        $decoded = json_decode($result, true);
        $this->assertSame(['amount' => 750, 'currency' => 'EUR'], $decoded);
    }

    public function test_set_array_with_amount_and_currency(): void
    {
        $result = $this->cast->set($this->model, 'price', ['amount' => 500, 'currency' => 'GBP'], []);

        $decoded = json_decode($result, true);
        $this->assertSame(['amount' => 500, 'currency' => 'GBP'], $decoded);
    }

    public function test_set_invalid_value_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot store value');

        $this->cast->set($this->model, 'price', 'raw-string', []);
    }

    public function test_set_invalid_array_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->cast->set($this->model, 'price', ['only_amount' => 100], []);
    }

    // -------------------------------------------------------------------------
    // Round-trip
    // -------------------------------------------------------------------------

    public function test_round_trip_money_instance(): void
    {
        $original = Money::USD(2499);

        $stored = $this->cast->set($this->model, 'price', $original, []);
        $retrieved = $this->cast->get($this->model, 'price', $stored, []);

        $this->assertInstanceOf(Money::class, $retrieved);
        $this->assertSame(2499, $retrieved->getAmount());
        $this->assertSame('USD', $retrieved->getCurrency()->getCode());
        $this->assertTrue($original->equals($retrieved));
    }

    public function test_round_trip_preserves_non_usd_currency(): void
    {
        $original = Money::EUR(9900);

        $stored = $this->cast->set($this->model, 'price', $original, []);
        $retrieved = $this->cast->get($this->model, 'price', $stored, []);

        $this->assertSame(9900, $retrieved->getAmount());
        $this->assertSame('EUR', $retrieved->getCurrency()->getCode());
    }
}
