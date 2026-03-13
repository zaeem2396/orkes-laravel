<?php

declare(strict_types=1);

namespace Conductor\Tests\Retry;

use Conductor\Retry\ExponentialDelayStrategy;
use PHPUnit\Framework\TestCase;

final class ExponentialDelayStrategyTest extends TestCase
{
    public function test_delay_for_attempt_zero_returns_initial_delay(): void
    {
        $strategy = new ExponentialDelayStrategy(1000, 2.0);

        $this->assertSame(1000, $strategy->delayForAttempt(0));
    }

    public function test_delay_for_attempt_one_returns_initial_times_multiplier(): void
    {
        $strategy = new ExponentialDelayStrategy(1000, 2.0);

        $this->assertSame(2000, $strategy->delayForAttempt(1));
    }

    public function test_delay_for_attempt_two_returns_exponential_backoff(): void
    {
        $strategy = new ExponentialDelayStrategy(1000, 2.0);

        $this->assertSame(4000, $strategy->delayForAttempt(2));
    }

    public function test_custom_initial_and_multiplier(): void
    {
        $strategy = new ExponentialDelayStrategy(500, 3.0);

        $this->assertSame(500, $strategy->delayForAttempt(0));
        $this->assertSame(1500, $strategy->delayForAttempt(1));
        $this->assertSame(4500, $strategy->delayForAttempt(2));
    }
}
