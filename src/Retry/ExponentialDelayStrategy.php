<?php

declare(strict_types=1);

namespace Conductor\Retry;

/**
 * Exponential backoff: initialDelay * (multiplier ^ attempt).
 */
final class ExponentialDelayStrategy implements DelayStrategy
{
    public function __construct(
        private readonly int $initialDelayMs = 1000,
        private readonly float $multiplier = 2.0,
    ) {
    }

    public function delayForAttempt(int $attempt): int
    {
        if ($attempt <= 0) {
            return $this->initialDelayMs;
        }

        $delay = (int) round($this->initialDelayMs * ($this->multiplier ** $attempt));

        return max(0, $delay);
    }
}
