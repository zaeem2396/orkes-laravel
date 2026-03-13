<?php

declare(strict_types=1);

namespace Conductor\Retry;

/**
 * Retry with configurable max attempts and delay strategy (exponential/linear).
 *
 * @internal Used by HttpClient and Worker.
 */
final class RetryHandler
{
    public function __construct(
        private int $maxAttempts = 3,
        private ?DelayStrategy $delayStrategy = null,
    ) {
        if ($this->delayStrategy === null) {
            $this->delayStrategy = new ExponentialDelayStrategy(1000, 2.0);
        }
    }

    /**
     * Execute callable with retries. On failure, waits according to delay strategy then retries.
     * If isRetryable is provided, only retries when it returns true for the thrown exception.
     *
     * @template T
     * @param  callable(): T  $fn
     * @param  null|callable(\Throwable): bool  $isRetryable
     * @return T
     *
     * @throws \Throwable Last exception after all attempts exhausted.
     */
    public function execute(callable $fn, ?callable $isRetryable = null): mixed
    {
        $lastException = null;
        $attempts = max(1, $this->maxAttempts);

        for ($attempt = 0; $attempt < $attempts; $attempt++) {
            try {
                return $fn();
            } catch (\Throwable $e) {
                $lastException = $e;
                if ($isRetryable !== null && ! $isRetryable($e)) {
                    throw $e;
                }
                if ($attempt < $attempts - 1) {
                    $delayMs = $this->delayStrategy->delayForAttempt($attempt);
                    if ($delayMs > 0) {
                        usleep($delayMs * 1000);
                    }
                }
            }
        }

        throw $lastException;
    }
}
