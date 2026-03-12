<?php

declare(strict_types=1);

namespace Conductor\Laravel\DSL;

/**
 * Fluent builder for a single workflow definition.
 *
 * @internal
 */
final class WorkflowDefinition
{
    /** @var list<string> */
    private array $tasks = [];

    public function __construct(
        private readonly string $name,
    ) {
    }

    public function task(string $taskRefName): self
    {
        $this->tasks[] = $taskRefName;

        return $this;
    }

    /**
     * Export as Conductor workflow definition (JSON-compatible array).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        // TODO: Build Conductor schema (tasks array, inputParameters, etc.).
        return [
            'name' => $this->name,
            'tasks' => $this->tasks,
        ];
    }
}
