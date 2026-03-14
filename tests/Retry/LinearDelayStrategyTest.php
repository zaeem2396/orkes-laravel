<?php

declare(strict_types=1);

namespace Conductor\Tests\Retry;

use Conductor\Retry\LinearDelayStrategy;
use PHPUnit\Framework\TestCase;

final class LinearDelayStrategyTest extends TestCase
{
    public function test_delay_for_attempt_zero_returns_initial_delay(): void
    {
        $strategy = new LinearDelayStrategy(1000, 500);

        $this->assertSame(1000, $strategy->delayForAttempt(0));
    }

    public function test_delay_for_attempt_one_returns_initial_plus_increment(): void
    {
        $strategy = new LinearDelayStrategy(1000, 500);

        $this->assertSame(1500, $strategy->delayForAttempt(1));
    }

    public function test_delay_for_attempt_two_returns_initial_plus_two_increments(): void
    {
        $strategy = new LinearDelayStrategy(1000, 500);

        $this->assertSame(2000, $strategy->delayForAttempt(2));
    }
}
