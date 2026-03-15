<?php

declare(strict_types=1);

namespace Conductor\Laravel\Testing;

/**
 * Fake worker for tests. listen() and run() / runOneCycle() are no-ops.
 * Returned by ConductorFake::workers() when using Conductor::fake().
 *
 * @internal
 */
final class FakeWorker
{
    /** No-op; returns $this for chaining. */
    public function listen(string $taskType, callable $handler): self
    {
        return $this;
    }

    /** No-op. */
    public function run(): void
    {
    }

    /** No-op. */
    public function runOneCycle(): void
    {
    }
}
