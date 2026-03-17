# Workflow example

This guide shows how to start Conductor workflows (Laravel and SDK), define them with the Workflow DSL, and use Artisan.

## Start a workflow (Laravel)

Using the Conductor facade (auto-discovered):

```php
use Conductor\Laravel\Facades\Conductor;

// Start by workflow definition name and optional input
$workflowId = Conductor::workflow()->start('order_processing', [
    'order_id' => 123,
    'customer_id' => 'cust-456',
]);

echo "Started: {$workflowId}\n";
```

## Start a workflow (standalone SDK)

```php
use Conductor\Client\ConductorClient;
use Conductor\Client\HttpClient;

$client = new ConductorClient(new HttpClient(
    'http://localhost:8080/api',
    getenv('CONDUCTOR_TOKEN') ?: null,
    30
));

$workflowId = $client->workflow()->start('order_processing', ['order_id' => 123]);
```

## Define a workflow with the DSL

Define workflows in PHP and register them with Conductor so they can be started by name:

```php
use Conductor\Laravel\DSL\Workflow;

$def = Workflow::define('order_processing')
    ->description('Process order: validate, charge, confirm')
    ->inputParameters(['order_id', 'customer_id'])
    ->task('validate_order')
    ->task('charge_payment')
    ->task('send_confirmation');

// Register with Conductor (Laravel)
$def->register(Conductor::workflow());

// Or get the JSON definition
$json = $def->toJson(JSON_PRETTY_PRINT);
```

See [DSL reference](dsl.md) and the runnable [examples/order_processing_workflow.php](../examples/order_processing_workflow.php).

## Artisan: start workflow from the command line

```bash
php artisan conductor:start order_processing --input='{"order_id":123}'
php artisan conductor:start order_processing --input='{"order_id":123}' --correlation-id=ord-1 --wf-version=2
```

## Get workflow status

After starting a workflow, you can fetch its state:

```php
$status = Conductor::workflow()->getWorkflow($workflowId, includeTasks: true);
// Or status only (no tasks)
$status = Conductor::workflow()->getWorkflowStatus($workflowId);
```

## Search workflows

Search by status, workflow type, etc.:

```php
$result = Conductor::workflow()->search('status = RUNNING', start: 0, size: 20);
$running = $result['results'];
$total = $result['totalHits'];
```

See the [README](../README.md) for more usage. Conductor API: [workflow](https://conductor-oss.github.io/conductor/documentation/api/workflow.html), [start workflow](https://conductor-oss.github.io/conductor/documentation/api/startworkflow.html).
