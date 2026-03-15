<?php

declare(strict_types=1);

namespace Conductor\Laravel\Testing;

/**
 * Fake task client for tests. poll() always returns null (no task); complete, fail, ack, update are no-ops.
 * Returned by ConductorFake::tasks() when using Conductor::fake(). Compatible surface with TaskClient for tests.
 *
 * @internal
 */
final class FakeTaskClient
{
    /**
     * Always returns null (no task available).
     *
     * @return array<string, mixed>|null
     */
    public function poll(string $taskType, ?string $workerId = null, ?string $domain = null): ?array
    {
        return null;
    }

    /** No-op. */
    public function complete(string $taskId, array $outputData = [], ?string $workflowInstanceId = null): void
    {
    }

    /** No-op. */
    public function fail(string $taskId, string $reasonForIncompletion, array $outputData = [], ?string $workflowInstanceId = null): void
    {
    }

    /** No-op. */
    public function ack(string $taskId, ?string $workflowInstanceId = null): void
    {
    }

    /** No-op. */
    public function update(string $taskId, string $status, array $outputData = [], ?string $reasonForIncompletion = null, ?int $callbackAfterSeconds = null, ?string $workflowInstanceId = null): void
    {
    }
}
