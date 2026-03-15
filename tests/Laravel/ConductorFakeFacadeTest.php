<?php

declare(strict_types=1);

namespace Conductor\Laravel\Tests;

use Conductor\Laravel\Facades\Conductor;

/**
 * Tests for Conductor::fake() with the Facade (Laravel app). Requires TestCase with ConductorServiceProvider.
 */
final class ConductorFakeFacadeTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('conductor', [
            'base_url' => 'https://conductor.example/api',
        ]);
    }

    public function test_fake_swaps_client_and_assert_workflow_started_passes(): void
    {
        Conductor::fake();

        Conductor::workflow()->start('order_processing', ['order_id' => 1]);

        Conductor::assertWorkflowStarted('order_processing');
        $this->addToAssertionCount(1);
    }

    public function test_fake_returns_same_instance_as_facade_root(): void
    {
        $fake = Conductor::fake();

        $this->assertSame($fake, Conductor::getFacadeRoot());
    }

    public function test_after_fake_tasks_poll_returns_null(): void
    {
        Conductor::fake();

        $this->assertNull(Conductor::tasks()->poll('any_task'));
    }

    public function test_after_fake_workflow_start_returns_fake_id(): void
    {
        Conductor::fake();

        $id = Conductor::workflow()->start('wf', []);

        $this->assertSame('fake-workflow-id', $id);
    }

    public function test_after_fake_workers_run_is_no_op(): void
    {
        Conductor::fake();

        Conductor::workers()->listen('task', fn () => [])->run();

        $this->addToAssertionCount(1);
    }
}
