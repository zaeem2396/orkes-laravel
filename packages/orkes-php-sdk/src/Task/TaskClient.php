<?php

declare(strict_types=1);

namespace Conductor\Task;

use Conductor\Client\HttpClient;

/**
 * Conductor task operations: poll, complete, fail, update, ack.
 */
final class TaskClient
{
    public function __construct(
        private readonly HttpClient $http,
    ) {
    }

    /**
     * Poll for the next task of the given type.
     *
     * @return array<string, mixed>|null
     */
    public function poll(string $taskType, ?string $workerId = null): ?array
    {
        // TODO: GET task/poll/{taskType}, return task or null.
        return null;
    }

    /**
     * Complete a task with output data.
     *
     * @param  array<string, mixed>  $outputData
     */
    public function complete(string $taskId, array $outputData = []): void
    {
        // TODO: POST task/{taskId}/complete
    }
}
