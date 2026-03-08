<?php

declare(strict_types=1);

namespace Conductor\Workflow;

use Conductor\Client\HttpClient;

/**
 * Conductor workflow operations.
 *
 * Methods: start, get, terminate, retry, pause, resume, status, registerDefinition, updateDefinition.
 *
 * @see https://conductor.netflix.io/api-docs/
 */
final class WorkflowClient
{
    public function __construct(
        private readonly HttpClient $http,
    ) {
    }

    /**
     * Start a workflow by definition name.
     *
     * @param  array<string, mixed>  $input
     */
    public function start(string $name, array $input = [], ?string $correlationId = null): string
    {
        // TODO: POST workflow/start, return workflowId.
        return '';
    }
}
