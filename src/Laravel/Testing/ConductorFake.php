<?php

declare(strict_types=1);

namespace Conductor\Laravel\Testing;

/**
 * Fake Conductor client for tests. Use via Conductor::fake() in Laravel or instantiate directly.
 *
 * Example:
 *   Conductor::fake();
 *   Conductor::workflow()->start('order_processing');
 *   Conductor::assertWorkflowStarted('order_processing');
 *
 * Assertions: assertWorkflowStarted, assertWorkflowStartedWithInput, assertNoWorkflowsStarted.
 * recordedStartedWorkflows() for custom assertions.
 */
final class ConductorFake
{
    /** @var list<array{name: string, input: array}> Recorded workflow starts for assertions. */
    private array $startedWorkflows = [];

    public function workflow(): FakeWorkflowClient
    {
        return new FakeWorkflowClient(function (string $name, array $input): void {
            $this->startedWorkflows[] = ['name' => $name, 'input' => $input];
        });
    }

    public function tasks(): FakeTaskClient
    {
        return new FakeTaskClient();
    }

    public function workers(): FakeWorker
    {
        return new FakeWorker();
    }

    /** Assert that a workflow with the given name was started (at least once). */
    public function assertWorkflowStarted(string $name): void
    {
        $found = false;
        foreach ($this->startedWorkflows as $w) {
            if ($w['name'] === $name) {
                $found = true;
                break;
            }
        }
        if (! $found) {
            throw new \RuntimeException("Expected workflow [{$name}] to be started.");
        }
    }

    /**
     * Assert a workflow was started with the given name and input (array subset).
     *
     * @param  array<string, mixed>  $input  Subset of input to match (all keys must match)
     */
    public function assertWorkflowStartedWithInput(string $name, array $input): void
    {
        foreach ($this->startedWorkflows as $w) {
            if ($w['name'] !== $name) {
                continue;
            }
            foreach ($input as $key => $value) {
                if (! array_key_exists($key, $w['input']) || $w['input'][$key] !== $value) {
                    continue 2;
                }
            }

            return;
        }
        throw new \RuntimeException("Expected workflow [{$name}] to be started with input matching " . json_encode($input));
    }

    /** Assert that no workflows were started. */
    public function assertNoWorkflowsStarted(): void
    {
        if ($this->startedWorkflows !== []) {
            $count = count($this->startedWorkflows);
            throw new \RuntimeException("Expected no workflows to be started, but {$count} were started.");
        }
    }

    /**
     * Return recorded started workflows for custom assertions.
     *
     * @return list<array{name: string, input: array}>
     */
    public function recordedStartedWorkflows(): array
    {
        return $this->startedWorkflows;
    }
}
