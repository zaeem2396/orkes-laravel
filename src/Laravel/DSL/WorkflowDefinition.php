<?php

declare(strict_types=1);

namespace Conductor\Laravel\DSL;

use Conductor\Workflow\WorkflowClient;

/**
 * Fluent builder for a single workflow definition.
 * Builds Conductor workflow definition (schemaVersion 2) with SIMPLE tasks.
 * Use Workflow::define($name) to create; then ->task(), ->description(), ->toArray(), ->toJson(), ->register().
 *
 * @internal
 */
final class WorkflowDefinition
{
    /** Conductor schema version (must be 2). */
    private const SCHEMA_VERSION = 2;

    /** Task type for worker tasks. */
    private const TASK_TYPE_SIMPLE = 'SIMPLE';

    /** @var list<string> */
    private array $taskNames = [];

    private string $description = '';

    private int $version = 1;

    private string $ownerEmail = 'conductor@example.com';

    /** @var list<string> */
    private array $inputParameters = [];

    /** @var array<string, mixed> */
    private array $outputParameters = [];

    public function __construct(
        private readonly string $name,
    ) {
    }

    /** Return the workflow name. */
    public function getName(): string
    {
        return $this->name;
    }

    /** Add a SIMPLE task to the workflow (appended to task list). */
    public function task(string $taskName): self
    {
        $this->taskNames[] = $taskName;

        return $this;
    }

    /** Set workflow description (optional). */
    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /** Set workflow definition version (default 1). */
    public function version(int $version): self
    {
        $this->version = $version;

        return $this;
    }

    /** Set owner email (required by Conductor; default conductor@example.com). */
    public function ownerEmail(string $ownerEmail): self
    {
        $this->ownerEmail = $ownerEmail;

        return $this;
    }

    /**
     * Set workflow input parameter names (documentation for Conductor; optional).
     *
     * @param  list<string>  $inputParameters
     */
    public function inputParameters(array $inputParameters): self
    {
        $this->inputParameters = array_values($inputParameters);

        return $this;
    }

    /**
     * Set workflow output parameters template (Conductor outputParameters).
     *
     * @param  array<string, mixed>  $outputParameters
     */
    public function outputParameters(array $outputParameters): self
    {
        $this->outputParameters = $outputParameters;

        return $this;
    }

    /**
     * Export as Conductor workflow definition (JSON-compatible array).
     * Tasks are SIMPLE type with taskReferenceName = taskName + '_ref'.
     * Includes name, version, tasks, schemaVersion, ownerEmail; optional description, inputParameters, outputParameters.
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
        if ($this->inputParameters !== []) {
            $def['inputParameters'] = $this->inputParameters;
        }
        if ($this->outputParameters !== []) {
            $def['outputParameters'] = $this->outputParameters;
        }

        return $def;
    }

    /**
     * Export as Conductor workflow definition JSON string.
     *
     * @param  int  $flags  JSON encode flags (e.g. JSON_PRETTY_PRINT)
     */
    public function toJson(int $flags = JSON_UNESCAPED_SLASHES): string
    {
        $json = json_encode($this->toArray(), $flags | JSON_THROW_ON_ERROR);

        return $json;
    }

    /**
     * Register this workflow definition with Conductor via WorkflowClient.
     * Calls registerWorkflowDefinition with toArray().
     */
    public function register(WorkflowClient $workflowClient): void
    {
        $workflowClient->registerWorkflowDefinition($this->toArray());
    }

    /** Conductor taskReferenceName: taskName + '_ref'. */
    private function taskReferenceName(string $taskName): string
    {
        return $taskName . '_ref';
    }
}
