<?php

declare(strict_types=1);

namespace Conductor\Task;

/**
 * Worker loop: poll for tasks and invoke registered handlers.
 *
 * Usage:
 *   $worker->listen('process_payment', function ($task) {
 *       return ['status' => 'COMPLETED', 'outputData' => ['payment_id' => 'abc123']];
 *   });
 *   $worker->run();
 *
 * Supports: infinite polling, sleep interval, retry, failure handling.
 */
final class Worker
{
    /** @var array<string, callable> */
    private array $handlers = [];

    public function __construct(
        private readonly TaskClient $taskClient,
        private int $pollIntervalSeconds = 5,
    ) {
    }

    public function listen(string $taskType, callable $handler): self
    {
        $this->handlers[$taskType] = $handler;

        return $this;
    }

    /**
     * Run the worker loop (infinite polling).
     */
    public function run(): void
    {
        // TODO: Poll, dispatch to handler, complete/fail task, sleep.
    }
}
