<?php

declare(strict_types=1);

namespace Conductor\Laravel\Testing;

/**
 * Fake worker for tests. listen() and run() / runOneCycle() are no-ops.
 *
 * @internal
 */
final class FakeWorker
{
    public function listen(string $taskType, callable $handler): self
    {
        return $this;
    }

    public function run(): void
    {
    }

    public function runOneCycle(): void
    {
    }
}
