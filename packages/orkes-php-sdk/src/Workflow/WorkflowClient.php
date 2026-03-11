<?php

declare(strict_types=1);

namespace Conductor\Workflow;

use Conductor\Client\HttpClient;

/**
 * Conductor workflow operations.
 *
 * Methods: startWorkflow, getWorkflow, terminateWorkflow, retryWorkflow,
 * pauseWorkflow, resumeWorkflow, getWorkflowStatus, registerWorkflowDefinition,
 * updateWorkflowDefinition.
 *
 * @see https://conductor-oss.github.io/conductor/documentation/api/workflow.html
 * @see https://conductor-oss.github.io/conductor/documentation/api/startworkflow.html
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
     *
     * @throws \Conductor\Exceptions\ConductorException
     */
    public function start(string $name, array $input = [], ?string $correlationId = null, ?int $version = null): string
    {
        $body = [
            'name' => $name,
            'input' => $input,
        ];
        if ($correlationId !== null) {
            $body['correlationId'] = $correlationId;
        }
        if ($version !== null) {
            $body['version'] = $version;
        }
        $result = $this->http->request('POST', 'workflow', $body);
        if (isset($result['workflowId'])) {
            return (string) $result['workflowId'];
        }

        throw new \Conductor\Exceptions\ConductorException(
            'Start workflow did not return a workflow ID (expected workflowId in response)',
            0,
        );
    }

    /**
     * Get workflow state by ID.
     *
     * @return array<string, mixed>
     *
     * @throws \Conductor\Exceptions\ConductorException
     */
    public function getWorkflow(string $workflowId, bool $includeTasks = true): array
    {
        return $this->http->request('GET', 'workflow/' . $workflowId, [
            'includeTasks' => $includeTasks ? 'true' : 'false',
        ]);
    }

    /**
     * Terminate a running workflow.
     *
     * @throws \Conductor\Exceptions\ConductorException
     */
    public function terminateWorkflow(string $workflowId): void
    {
        $this->http->request('DELETE', 'workflow/' . $workflowId, []);
    }

    /**
     * Retry the last failed task in a workflow.
     *
     * @throws \Conductor\Exceptions\ConductorException
     */
    public function retryWorkflow(string $workflowId): void
    {
        $this->http->request('POST', 'workflow/' . $workflowId . '/retry', []);
    }

    /**
     * Pause a running workflow.
     *
     * @throws \Conductor\Exceptions\ConductorException
     */
    public function pauseWorkflow(string $workflowId): void
    {
        $this->http->request('PUT', 'workflow/' . $workflowId . '/pause', []);
    }

    /**
     * Resume a paused workflow.
     *
     * @throws \Conductor\Exceptions\ConductorException
     */
    public function resumeWorkflow(string $workflowId): void
    {
        $this->http->request('PUT', 'workflow/' . $workflowId . '/resume', []);
    }

    /**
     * Get workflow status (convenience for getWorkflow with includeTasks=false).
     *
     * @return array<string, mixed>
     *
     * @throws \Conductor\Exceptions\ConductorException
     */
    public function getWorkflowStatus(string $workflowId): array
    {
        return $this->getWorkflow($workflowId, false);
    }

    /**
     * Register a workflow definition.
     *
     * @param  array<string, mixed>  $definition
     *
     * @throws \Conductor\Exceptions\ConductorException
     */
    public function registerWorkflowDefinition(array $definition): void
    {
        $this->http->request('POST', 'metadata/workflow', $definition);
    }

    /**
     * Update workflow definition(s). Accepts a list of definitions.
     *
     * @param  array<int, array<string, mixed>>  $definitions
     *
     * @throws \Conductor\Exceptions\ConductorException
     */
    public function updateWorkflowDefinition(array $definitions): void
    {
        $this->http->request('PUT', 'metadata/workflow', $definitions);
    }
}
