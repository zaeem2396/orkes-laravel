# Testing

Use the Conductor fake to test code that starts workflows or uses the Conductor client without calling the real Conductor API. See also [README](../README.md) Testing section.

## Conductor::fake()

In Laravel tests, call `Conductor::fake()` to replace the real client with a fake. The fake records started workflows and provides assertion methods.

```php
use Conductor\Laravel\Facades\Conductor;

public function test_order_flow_starts_workflow(): void
{
    Conductor::fake();

    // Your code that starts a workflow:
    Conductor::workflow()->start('order_processing', ['order_id' => 123]);

    Conductor::assertWorkflowStarted('order_processing');
    Conductor::assertWorkflowStartedWithInput('order_processing', ['order_id' => 123]);
}
```

## Assertion methods

- **assertWorkflowStarted(string $name)** — Asserts at least one workflow with the given name was started. Throws RuntimeException if not found.
- **assertWorkflowStartedWithInput(string $name, array $input)** — Asserts a workflow was started with the given name and that its input contains the given key/value subset.
- **assertNoWorkflowsStarted()** — Asserts no workflows were started. Throws RuntimeException if any were started.
- **recordedStartedWorkflows()** — Returns the list of recorded started workflows (`[['name' => '...', 'input' => [...]], ...]`) for custom assertions.

## Using the fake without the Facade

You can instantiate `Conductor\Laravel\Testing\ConductorFake` directly (e.g. in unit tests that do not bootstrap Laravel):

```php
use Conductor\Laravel\Testing\ConductorFake;

$fake = new ConductorFake();
$fake->workflow()->start('order_processing', []);
$fake->assertWorkflowStarted('order_processing');
```

## Tasks and workers

When using the fake, `Conductor::tasks()->poll()` always returns `null` (no task available). `Conductor::workers()->listen(...)->run()` is a no-op. This lets you test workflow-starting code without running workers.

## PHPUnit

Run all tests: `composer test` or `./vendor/bin/phpunit`. Run only Laravel/Conductor fake tests: `./vendor/bin/phpunit tests/Laravel/ConductorFakeTest.php tests/Laravel/ConductorFakeFacadeTest.php`.
