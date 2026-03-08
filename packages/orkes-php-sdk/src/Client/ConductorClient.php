<?php

declare(strict_types=1);

namespace Conductor\Client;

use Conductor\Task\TaskClient;
use Conductor\Task\Worker;
use Conductor\Workflow\WorkflowClient;

/**
 * Main SDK entrypoint for Conductor / Orkes.
 *
 * Usage:
 *   $client = new ConductorClient([
 *       'base_url' => 'http://localhost:8080/api',
 *       'token'    => 'xyz',
 *   ]);
 *   $client->workflow()->start('order_processing', ['order_id' => 123]);
 */
final class ConductorClient
{
    private ?WorkflowClient $workflowClient = null;

    private ?TaskClient $taskClient = null;

    public function __construct(
        private readonly HttpClient $http,
    ) {
    }

    public function workflow(): WorkflowClient
    {
        if ($this->workflowClient === null) {
            $this->workflowClient = new WorkflowClient($this->http);
        }

        return $this->workflowClient;
    }

    public function tasks(): TaskClient
    {
        if ($this->taskClient === null) {
            $this->taskClient = new TaskClient($this->http);
        }

        return $this->taskClient;
    }

    /**
     * Access worker factory / runner (uses TaskClient internally).
     */
    public function workers(): Worker
    {
        return new Worker($this->tasks());
    }
}
