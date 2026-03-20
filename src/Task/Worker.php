<?php

declare(strict_types=1);

namespace Conductor\Task;

use Conductor\Exceptions\TaskException;

/**
 * Worker loop: poll for tasks and invoke registered handlers.
 *
 * Usage:
 *   $worker->listen('process_payment', function (array $task) {
 *       return ['status' => 'COMPLETED', 'outputData' => ['payment_id' => 'abc123']];
 *   });
 *   $worker->run();
 *
 * Handler return: array with 'status' => 'COMPLETED'|'FAILED', 'outputData' => array.
 * For FAILED, include 'reasonForIncompletion' => string.
 * Supports: infinite polling, sleep interval, optional retry on handler exception.
 */
final class Worker
{
    private const STATUS_COMPLETED = 'COMPLETED';

    private const STATUS_FAILED = 'FAILED';

    /** @var array<string, callable(array): array> */
    private array $handlers = [];

    public function __construct(
        private readonly TaskClient $taskClient,
        private int $pollIntervalSeconds = 5,
        private ?string $workerId = null,
        private ?string $domain = null,
        private int $maxRetries = 0,
    ) {
    }

    /**
     * Register a handler for the given task type.
     *
     * @param  callable(array<string, mixed>): array{status: string, outputData?: array<string, mixed>, reasonForIncompletion?: string, terminal?: bool}  $handler
     */
    public function listen(string $taskType, callable $handler): self
    {
        $this->handlers[$taskType] = $handler;

        return $this;
    }

    /**
     * Run the worker loop (infinite polling).
     * Polls each registered task type once per cycle, processes one task if available, then sleeps.
     *
     * @throws TaskException On task update (complete/fail) errors.
     */
    public function run(): void
    {
        while (true) {
            $this->runOneCycle();
            if ($this->pollIntervalSeconds > 0) {
                sleep($this->pollIntervalSeconds);
            }
        }
    }

    /**
     * Run a single poll cycle: poll each task type once, process at most one task.
     *
     * @throws TaskException
     */
    public function runOneCycle(): void
    {
        foreach (array_keys($this->handlers) as $taskType) {
            $task = $this->taskClient->poll($taskType, $this->workerId, $this->domain);
            if ($task === null) {
                continue;
            }

            $this->processTask($taskType, $task);

            return;
        }
    }

    /**
     * Process a single task: ack, invoke handler, complete or fail.
     *
     * @param  array<string, mixed>  $task
     *
     * @throws TaskException
     */
    private function processTask(string $taskType, array $task): void
    {
        $taskId = $task['taskId'] ?? '';
        // Poll payloads use workflowInstanceId; some stacks expose workflowId only.
        $workflowInstanceId = $task['workflowInstanceId'] ?? $task['workflowId'] ?? null;
        $workflowInstanceId = $workflowInstanceId !== null && $workflowInstanceId !== ''
            ? (string) $workflowInstanceId
            : null;
        $handler = $this->handlers[$taskType];

        if ($taskId === '') {
            return;
        }

        if ($workflowInstanceId === null || $workflowInstanceId === '') {
            throw new TaskException(
                'Polled task is missing workflowInstanceId (and workflowId); cannot update task on Conductor.',
                0,
            );
        }

        $this->taskClient->ack($taskId, $workflowInstanceId);

        $lastException = null;
        $result = null;
        $attempts = $this->maxRetries + 1;

        for ($attempt = 0; $attempt < $attempts; $attempt++) {
            try {
                $result = $handler($task);
                break;
            } catch (\Throwable $e) {
                $lastException = $e;
                if ($attempt === $attempts - 1) {
                    $this->taskClient->fail(
                        $taskId,
                        'Handler failed: ' . $e->getMessage(),
                        [],
                        $workflowInstanceId,
                    );

                    return;
                }
            }
        }

        if ($result === null) {
            return;
        }

        $status = $result['status'] ?? '';
        $outputData = $result['outputData'] ?? [];

        if ($status === self::STATUS_COMPLETED) {
            $this->taskClient->complete($taskId, $outputData, $workflowInstanceId);

            return;
        }

        if ($status === self::STATUS_FAILED) {
            $reason = $result['reasonForIncompletion'] ?? 'Failed';
            $terminal = (bool) ($result['terminal'] ?? false);
            $this->taskClient->fail($taskId, $reason, $outputData, $workflowInstanceId, $terminal);

            return;
        }

        $this->taskClient->fail(
            $taskId,
            'Invalid handler status: ' . $status,
            $outputData,
            $workflowInstanceId,
        );
    }
}
