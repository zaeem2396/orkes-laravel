<?php

declare(strict_types=1);

namespace Conductor\Workflow;

use Conductor\Client\HttpClient;
use Conductor\Exceptions\WorkflowException;

/**
 * Conductor workflow operations.
 *
 * Methods: start, getWorkflow, terminateWorkflow, retryWorkflow, pauseWorkflow,
 * resumeWorkflow, getWorkflowStatus, registerWorkflowDefinition,
 * updateWorkflowDefinition, search.
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
     * @throws WorkflowException
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

        throw new WorkflowException(
            'Start workflow did not return a workflow ID (expected workflowId in response)',
            0,
        );
    }

    /**
     * Get workflow state by ID.
     *
     * @return array<string, mixed>
     *
     * @throws WorkflowException
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
     * @throws WorkflowException
     */
    public function terminateWorkflow(string $workflowId): void
    {
        $this->http->request('DELETE', 'workflow/' . $workflowId, []);
    }

    /**
     * Retry the last failed task in a workflow.
     *
     * @throws WorkflowException
     */
    public function retryWorkflow(string $workflowId): void
    {
        $this->http->request('POST', 'workflow/' . $workflowId . '/retry', []);
    }

    /**
     * Pause a running workflow.
     *
     * @throws WorkflowException
     */
    public function pauseWorkflow(string $workflowId): void
    {
        $this->http->request('PUT', 'workflow/' . $workflowId . '/pause', []);
    }

    /**
     * Resume a paused workflow.
     *
     * @throws WorkflowException
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
     * @throws WorkflowException
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
     * @throws WorkflowException
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
     * @throws WorkflowException
     */
    public function updateWorkflowDefinition(array $definitions): void
    {
        $this->http->request('PUT', 'metadata/workflow', $definitions);
    }

    /**
     * Search workflow executions (GET /workflow/search).
     * Query examples: "status = RUNNING", "status IN (FAILED, TIMED_OUT)", "workflowType = my_workflow".
     *
     * @return array{totalHits: int, results: array<int, array<string, mixed>>}
     *
     * @throws WorkflowException
     */
    public function search(string $query, int $start = 0, int $size = 100, string $sort = 'startTime:DESC', string $freeText = '*'): array
    {
        $result = $this->http->request('GET', 'workflow/search', [
            'query' => $query,
            'start' => $start,
            'size' => $size,
            'sort' => $sort,
            'freeText' => $freeText,
        ]);

        return [
            'totalHits' => (int) ($result['totalHits'] ?? 0),
            'results' => isset($result['results']) && is_array($result['results']) ? $result['results'] : [],
        ];
    }
}
