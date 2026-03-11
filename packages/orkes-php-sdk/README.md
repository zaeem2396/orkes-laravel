# Conductor Orkes PHP SDK

Framework-agnostic PHP SDK for Conductor workflows (Orkes Conductor Cloud and Netflix Conductor). Supports PHP 8.2, 8.3, and 8.4.

## Installation

```bash
composer require conductor/orkes-php-sdk
```

## Usage

```php
use Conductor\Client\ConductorClient;
use Conductor\Client\HttpClient;

$client = new ConductorClient(
    new HttpClient(
        baseUrl: 'http://localhost:8080/api',
        token: 'your-token',
    )
);

$client->workflow()->start('order_processing', ['order_id' => 123]);
```

## Workflow API

The workflow client provides full lifecycle operations:

```php
// Start (returns workflow ID)
$workflowId = $client->workflow()->start('order_processing', ['order_id' => 123], 'correlation-1', 1);

// Get state (optionally include tasks)
$state = $client->workflow()->getWorkflow($workflowId, includeTasks: true);

// Status only
$status = $client->workflow()->getWorkflowStatus($workflowId);

// Lifecycle: pause, resume, retry, terminate
$client->workflow()->pauseWorkflow($workflowId);
$client->workflow()->resumeWorkflow($workflowId);
$client->workflow()->retryWorkflow($workflowId);
$client->workflow()->terminateWorkflow($workflowId);

// Register or update workflow definitions
$client->workflow()->registerWorkflowDefinition(['name' => 'my_wf', 'tasks' => [], 'version' => 1]);
$client->workflow()->updateWorkflowDefinition([$definition1, $definition2]);
```

## Task API

The task client supports polling for tasks and submitting results:

```php
$tasks = $client->tasks();

// Poll for a task (returns task array or null when none available)
$task = $tasks->poll('process_payment');
$task = $tasks->poll('process_payment', workerId: 'worker-1', domain: 'domain-a');

if ($task !== null) {
    $taskId = $task['taskId'];
    $workflowInstanceId = $task['workflowInstanceId'] ?? null;

    // Acknowledge (extend lease)
    $tasks->ack($taskId, $workflowInstanceId);

    // Complete with output
    $tasks->complete($taskId, ['payment_id' => 'p123'], $workflowInstanceId);

    // Or fail with reason
    $tasks->fail($taskId, 'Payment declined', ['code' => 'DECLINED'], $workflowInstanceId);

    // Or update status / output / callback
    $tasks->update($taskId, 'IN_PROGRESS', ['progress' => 50], null, 60, $workflowInstanceId);
}
```

Task-related errors are thrown as `Conductor\Exceptions\TaskException`.

## Roadmap

See [docs/ROADMAP.md](../../docs/ROADMAP.md) in the monorepo root.
