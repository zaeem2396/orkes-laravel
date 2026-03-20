<?php

declare(strict_types=1);

namespace Conductor\Laravel\Testing;

/**
 * Fake workflow client that records started workflows for assertions.
 * start() returns 'fake-workflow-id' and records name/input via callback.
 * registerWorkflowDefinition / updateWorkflowDefinition are no-ops; getWorkflow returns a minimal stub.
 *
 * @internal
 */
final class FakeWorkflowClient
{
    /**
     * @param  callable(string, array): void  $onStart
     */
    public function __construct(
        private readonly mixed $onStart,
    ) {
    }

    /**
     * Records the start call and returns 'fake-workflow-id'.
     *
     * @param  array<string, mixed>  $input
     */
    public function start(string $name, array $input = [], ?string $correlationId = null, ?int $version = null): string
    {
        ($this->onStart)($name, $input);

        return 'fake-workflow-id';
    }

    /**
     * No-op: real client POSTs metadata/workflow.
     *
     * @param  array<string, mixed>  $definition
     */
    public function registerWorkflowDefinition(array $definition): void
    {
        // Intentionally empty — tests avoid hitting Conductor.
    }

    /**
     * No-op: real client PUTs metadata/workflow.
     *
     * @param  array<int, array<string, mixed>>  $definitions
     */
    public function updateWorkflowDefinition(array $definitions): void
    {
        // Intentionally empty — tests avoid hitting Conductor.
    }

    /**
     * Stub response for status polling in apps that call getWorkflow() in tests.
     *
     * @return array<string, mixed>
     */
    public function getWorkflow(string $workflowId, bool $includeTasks = true): array
    {
        return [
            'workflowId' => $workflowId,
            'status' => 'RUNNING',
            'workflowType' => 'order_processing',
            'tasks' => [],
        ];
    }
}
