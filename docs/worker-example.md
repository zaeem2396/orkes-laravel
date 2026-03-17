# Worker example

This guide shows how to run Conductor task workers: implement TaskHandler, register in config, and run via Artisan or in code.

## Implement a task handler (Laravel)

Create a class that implements `Conductor\Laravel\Workers\TaskHandler` and register it in config:

```php
namespace App\Workers;

use Conductor\Laravel\Workers\TaskHandler;

final class ProcessPaymentTaskHandler implements TaskHandler
{
    public function taskType(): string
    {
        return 'process_payment';
    }

    /**
     * @param array<string, mixed> $task  Conductor task payload
     * @return array<string, mixed>       Output data for COMPLETED
     */
    public function handle(array $task): array
    {
        $orderId = $task['inputData']['order_id'] ?? null;
        // Your business logic...
        return [
            'payment_id' => 'pay-123',
            'status' => 'charged',
        ];
    }
}
```

To fail the task, throw an exception from `handle()`. The worker will call `fail()` with the exception message.

## Register handlers in config

In `config/conductor.php`, add your handler class names to `task_handlers` so `conductor:work` can resolve them:

```php
'task_handlers' => [
    \App\Workers\ProcessPaymentTaskHandler::class,
    \App\Workers\SendConfirmationTaskHandler::class,
],
```

## Run the worker (Artisan)

Run the worker daemon (infinite loop; polls for tasks and runs handlers):

```bash
php artisan conductor:work
```

Limit to one task type and optional queue (domain):

```bash
php artisan conductor:work --task=process_payment --queue=my-queue
```

For local development with a single poll cycle:

```bash
php artisan conductor:local --once
```

## Worker usage in code (standalone SDK)

Without Laravel, you can run a worker in code:

```php
use Conductor\Client\ConductorClient;
use Conductor\Client\HttpClient;

$client = new ConductorClient(new HttpClient('http://localhost:8080/api', 'token', 30));

$worker = $client->workers();
$worker->listen('process_payment', function (array $task) {
    // Process $task, return output
    return ['status' => 'COMPLETED', 'outputData' => ['payment_id' => 'pay-123']];
});
$worker->run(); // infinite loop; use runOneCycle() for a single poll
```

Handler return format: `['status' => 'COMPLETED'|'FAILED', 'outputData' => array]`. For FAILED, include `'reasonForIncompletion' => string`.

## Task definitions in Conductor

Ensure your task types (e.g. `process_payment`) are registered as task definitions in Conductor before starting workflows that use them. The package does not register task definitions; only workflow definitions (e.g. via the DSL). The workflow DSL only registers workflow definitions; task definitions are registered separately in Conductor.

See the [README](../README.md) for Artisan commands and [testing](testing.md) for using `Conductor::fake()` so workers are not needed in tests. Task definitions: [Conductor task API](https://conductor-oss.github.io/conductor/documentation/api/task.html).
