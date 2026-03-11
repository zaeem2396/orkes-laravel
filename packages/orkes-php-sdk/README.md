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

## Roadmap

See [docs/ROADMAP.md](../../docs/ROADMAP.md) in the monorepo root.
