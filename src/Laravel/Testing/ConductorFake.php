<?php

declare(strict_types=1);

namespace Conductor\Laravel\Testing;

/**
 * Fake Conductor client for tests. Use via Conductor::fake().
 *
 * Example:
 *   Conductor::fake();
 *   Conductor::workflow()->start('order_processing');
 *   Conductor::assertWorkflowStarted('order_processing');
 */
final class ConductorFake
{
    /** @var list<array{name: string, input: array}> */
    private array $startedWorkflows = [];

    public function workflow(): FakeWorkflowClient
    {
        return new FakeWorkflowClient(function (string $name, array $input): void {
            $this->startedWorkflows[] = ['name' => $name, 'input' => $input];
        });
    }

    public function tasks(): void
    {
        // Stub for tests that only assert workflow started.
    }

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
}
