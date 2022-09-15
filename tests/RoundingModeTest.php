<?php

declare(strict_types=1);

namespace PhilipRehberger\Money\Tests;

use PhilipRehberger\Money\Money;
use PhilipRehberger\Money\RoundingMode;
use PHPUnit\Framework\TestCase;

class RoundingModeTest extends TestCase
{
    public function test_multiply_default_is_half_up(): void
    {
        // 1000 * 1.005 = 1005.0 (exact), no rounding difference
        $result = Money::USD(1000)->multiply(1.005);

        $this->assertSame(1005, $result->getAmount());
    }

    public function test_multiply_half_up(): void
    {
        // 999 * 1.005 = 1003.995 → rounds to 1004
        $result = Money::USD(999)->multiply(1.005, RoundingMode::HALF_UP);

        $this->assertSame(1004, $result->getAmount());
    }

    public function test_multiply_half_down(): void
    {
        // 999 * 1.005 = 1003.995 → HALF_DOWN still rounds 1003.995 to 1004 (not at .5 boundary)
        // Use a value that hits exactly .5: 1 * 1.5 = 1.5
        // HALF_UP: 2, HALF_DOWN: 1
        $resultUp = Money::USD(1)->multiply(1.5, RoundingMode::HALF_UP);
        $resultDown = Money::USD(1)->multiply(1.5, RoundingMode::HALF_DOWN);

        $this->assertSame(2, $resultUp->getAmount());
        $this->assertSame(1, $resultDown->getAmount());
    }

    public function test_multiply_half_even(): void
    {
        // Banker's rounding: 1 * 1.5 = 1.5 → rounds to 2 (nearest even)
        // 1 * 2.5 = 2.5 → rounds to 2 (nearest even)
        $result1 = Money::USD(1)->multiply(1.5, RoundingMode::HALF_EVEN);
        $result2 = Money::USD(1)->multiply(2.5, RoundingMode::HALF_EVEN);

        $this->assertSame(2, $result1->getAmount());
        $this->assertSame(2, $result2->getAmount());
    }

    public function test_multiply_ceiling(): void
    {
        // 999 * 1.005 = 1003.995 → ceil = 1004
        $result = Money::USD(999)->multiply(1.005, RoundingMode::CEILING);

        $this->assertSame(1004, $result->getAmount());

        // Negative: -999 * 1.005 = -1003.995 → ceil = -1003
        $resultNeg = Money::USD(-999)->multiply(1.005, RoundingMode::CEILING);

        $this->assertSame(-1003, $resultNeg->getAmount());
    }

    public function test_multiply_floor(): void
    {
        // 999 * 1.005 = 1003.995 → floor = 1003
        $result = Money::USD(999)->multiply(1.005, RoundingMode::FLOOR);

        $this->assertSame(1003, $result->getAmount());

        // Negative: -999 * 1.005 = -1003.995 → floor = -1004
        $resultNeg = Money::USD(-999)->multiply(1.005, RoundingMode::FLOOR);

        $this->assertSame(-1004, $resultNeg->getAmount());
    }

    public function test_divide_with_rounding_mode(): void
    {
        // 1001 / 2 = 500.5
        // HALF_UP → 501, HALF_DOWN → 500, FLOOR → 500, CEILING → 501
        $this->assertSame(501, Money::USD(1001)->divide(2, RoundingMode::HALF_UP)->getAmount());
        $this->assertSame(500, Money::USD(1001)->divide(2, RoundingMode::HALF_DOWN)->getAmount());
        $this->assertSame(500, Money::USD(1001)->divide(2, RoundingMode::FLOOR)->getAmount());
        $this->assertSame(501, Money::USD(1001)->divide(2, RoundingMode::CEILING)->getAmount());
    }

    public function test_rounding_modes_produce_different_results(): void
    {
        // 1 * 1.5 = 1.5 — this is exactly at the .5 boundary
        $halfUp = Money::USD(1)->multiply(1.5, RoundingMode::HALF_UP)->getAmount();
        $halfDown = Money::USD(1)->multiply(1.5, RoundingMode::HALF_DOWN)->getAmount();
        $floor = Money::USD(1)->multiply(1.5, RoundingMode::FLOOR)->getAmount();
        $ceiling = Money::USD(1)->multiply(1.5, RoundingMode::CEILING)->getAmount();

        $this->assertSame(2, $halfUp);
        $this->assertSame(1, $halfDown);
        $this->assertSame(1, $floor);
        $this->assertSame(2, $ceiling);
    }
}
