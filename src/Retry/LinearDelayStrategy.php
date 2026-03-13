<?php

declare(strict_types=1);

namespace Conductor\Retry;

/**
 * Linear delay: initialDelay + (increment * attempt).
 */
final class LinearDelayStrategy implements DelayStrategy
{
    public function __construct(
        private readonly int $initialDelayMs = 1000,
        private readonly int $incrementMs = 500,
    ) {
    }

    public function delayForAttempt(int $attempt): int
    {
        if ($attempt <= 0) {
            return $this->initialDelayMs;
        }

        return $this->initialDelayMs + ($this->incrementMs * $attempt);
    }
}
