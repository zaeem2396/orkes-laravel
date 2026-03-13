<?php

declare(strict_types=1);

namespace Conductor\Retry;

/**
 * Strategy for computing delay (ms) before the next retry attempt.
 */
interface DelayStrategy
{
    /**
     * Return delay in milliseconds for the given attempt (0-based).
     */
    public function delayForAttempt(int $attempt): int;
}
