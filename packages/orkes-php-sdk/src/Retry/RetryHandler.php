<?php

declare(strict_types=1);

namespace Conductor\Retry;

/**
 * Retry with exponential backoff, max attempts, configurable delay strategy.
 *
 * @internal Used by HttpClient and Worker.
 */
final class RetryHandler
{
    public function __construct(
        private int $maxAttempts = 3,
        private int $initialDelayMs = 1000,
    ) {
    }

    /**
     * Execute callable with retries.
     *
     * @template T
     * @param  callable(): T  $fn
     * @return T
     */
    public function execute(callable $fn): mixed
    {
        // TODO: Exponential backoff, delay strategy.
        return $fn();
    }
}
