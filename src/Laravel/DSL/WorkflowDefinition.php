<?php

declare(strict_types=1);

namespace Conductor\Laravel\DSL;

use Conductor\Workflow\WorkflowClient;

/**
 * Fluent builder for a single workflow definition.
 * Builds Conductor workflow definition (schemaVersion 2) with SIMPLE tasks.
 *
 * @internal
 */
final class WorkflowDefinition
{
    private const SCHEMA_VERSION = 2;

    private const TASK_TYPE_SIMPLE = 'SIMPLE';

    /** @var list<string> */
    private array $taskNames = [];

    private string $description = '';

    private int $version = 1;

    private string $ownerEmail = 'conductor@example.com';

    public function __construct(
        private readonly string $name,
    ) {
    }

    public function task(string $taskName): self
    {
        $this->taskNames[] = $taskName;

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function version(int $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function ownerEmail(string $ownerEmail): self
    {
        $this->ownerEmail = $ownerEmail;

        return $this;
    }

    /**
     * Export as Conductor workflow definition (JSON-compatible array).
     * Tasks are SIMPLE type with taskReferenceName = taskName + '_ref'.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $tasks = [];
        foreach ($this->taskNames as $taskName) {
            $taskRefName = $this->taskReferenceName($taskName);
            $tasks[] = [
                'name' => $taskName,
                'taskReferenceName' => $taskRefName,
                'type' => self::TASK_TYPE_SIMPLE,
            ];
        }

        $def = [
            'name' => $this->name,
            'version' => $this->version,
            'tasks' => $tasks,
            'schemaVersion' => self::SCHEMA_VERSION,
            'ownerEmail' => $this->ownerEmail,
        ];
        if ($this->description !== '') {
            $def['description'] = $this->description;
        }

        return $def;
    }

    /**
     * Export as Conductor workflow definition JSON string.
     */
    public function toJson(int $flags = JSON_UNESCAPED_SLASHES): string
    {
        $json = json_encode($this->toArray(), $flags | JSON_THROW_ON_ERROR);

        return $json;
    }

    /**
     * Register this workflow definition with Conductor via WorkflowClient.
     */
    public function register(WorkflowClient $workflowClient): void
    {
        $workflowClient->registerWorkflowDefinition($this->toArray());
    }

    private function taskReferenceName(string $taskName): string
    {
        return $taskName . '_ref';
    }
}
