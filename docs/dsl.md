# Workflow DSL

Define Conductor workflows in PHP with a fluent API. Generates Conductor schema version 2 definitions with SIMPLE (worker) tasks.

## Usage

```php
use Conductor\Laravel\DSL\Workflow;

$def = Workflow::define('order_processing')
    ->description('Order processing workflow')
    ->task('validate_order')
    ->task('charge_payment')
    ->task('send_confirmation');
```

## Methods

- `Workflow::define(string $name)` — Start a new workflow definition.
- `->task(string $taskName)` — Add a SIMPLE task (chainable).
- `->description(string)`, `->version(int)`, `->ownerEmail(string)` — Optional metadata.
- `->inputParameters(array)` — List of input parameter names (documentation). `->outputParameters(array)` — Output template (e.g. JSONPath expressions).
- `->toArray()` — Conductor workflow definition as array.
- `->toJson(int $flags)` — JSON string.
- `->register(WorkflowClient $client)` — Register with Conductor.

## Example

See [examples/order_processing_workflow.php](../examples/order_processing_workflow.php) and [examples/README.md](../examples/README.md).
