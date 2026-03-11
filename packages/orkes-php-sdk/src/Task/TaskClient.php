<?php

declare(strict_types=1);

namespace Conductor\Task;

use Conductor\Client\HttpClient;
use Conductor\Exceptions\TaskException;

/**
 * Conductor task operations: poll, complete, fail, update, ack.
 *
 * @see https://conductor-oss.github.io/conductor/documentation/api/task.html
 */
final class TaskClient
{
    private const STATUS_IN_PROGRESS = 'IN_PROGRESS';

    private const STATUS_COMPLETED = 'COMPLETED';

    private const STATUS_FAILED = 'FAILED';

    public function __construct(
        private readonly HttpClient $http,
    ) {
    }

    /**
     * Poll for the next task of the given type.
     * Returns null when no task is available (204).
     *
     * @return array<string, mixed>|null
     *
     * @throws TaskException
     */
    public function poll(string $taskType, ?string $workerId = null, ?string $domain = null): ?array
    {
        $query = [];
        if ($workerId !== null && $workerId !== '') {
            $query['workerid'] = $workerId;
        }
        if ($domain !== null && $domain !== '') {
            $query['domain'] = $domain;
        }
        $result = $this->http->request('GET', 'tasks/poll/' . $taskType, $query);

        if ($result === []) {
            return null;
        }

        return $result;
    }

    /**
     * Complete a task with output data.
     *
     * @param  array<string, mixed>  $outputData
     *
     * @throws TaskException
     */
    public function complete(string $taskId, array $outputData = [], ?string $workflowInstanceId = null): void
    {
        $this->updateTask($taskId, self::STATUS_COMPLETED, $outputData, null, $workflowInstanceId);
    }

    /**
     * Mark a task as failed.
     *
     * @param  array<string, mixed>  $outputData  Optional output to store.
     *
     * @throws TaskException
     */
    public function fail(string $taskId, string $reasonForIncompletion, array $outputData = [], ?string $workflowInstanceId = null): void
    {
        $this->updateTask($taskId, self::STATUS_FAILED, $outputData, $reasonForIncompletion, $workflowInstanceId);
    }

    /**
     * Update task (e.g. IN_PROGRESS with partial output or callback).
     *
     * @param  array<string, mixed>  $outputData
     *
     * @throws TaskException
     */
    public function update(string $taskId, string $status, array $outputData = [], ?string $reasonForIncompletion = null, ?int $callbackAfterSeconds = null, ?string $workflowInstanceId = null): void
    {
        $this->updateTask($taskId, $status, $outputData, $reasonForIncompletion, $workflowInstanceId, $callbackAfterSeconds);
    }

    /**
     * Acknowledge task (extend lease). Sends IN_PROGRESS to indicate worker is processing.
     * Conductor no longer has a dedicated ack endpoint; this uses the task update API.
     *
     * @throws TaskException
     */
    public function ack(string $taskId, ?string $workflowInstanceId = null): void
    {
        $this->updateTask($taskId, self::STATUS_IN_PROGRESS, [], null, $workflowInstanceId);
    }

    /**
     * @param  array<string, mixed>  $outputData
     *
     * @throws TaskException
     */
    private function updateTask(
        string $taskId,
        string $status,
        array $outputData = [],
        ?string $reasonForIncompletion = null,
        ?string $workflowInstanceId = null,
        ?int $callbackAfterSeconds = null,
    ): void {
        $body = [
            'taskId' => $taskId,
            'status' => $status,
            'outputData' => $outputData,
        ];
        if ($workflowInstanceId !== null && $workflowInstanceId !== '') {
            $body['workflowInstanceId'] = $workflowInstanceId;
        }
        if ($reasonForIncompletion !== null) {
            $body['reasonForIncompletion'] = $reasonForIncompletion;
        }
        if ($callbackAfterSeconds !== null) {
            $body['callbackAfterSeconds'] = $callbackAfterSeconds;
        }
        try {
            $this->http->request('POST', 'tasks', $body);
        } catch (\Conductor\Exceptions\ConductorException $e) {
            throw new TaskException(
                'Task update failed: ' . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }
}
