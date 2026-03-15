<?php

declare(strict_types=1);

namespace Conductor\Laravel\Tests;

use Conductor\Laravel\Facades\Conductor;

/**
 * Tests for Conductor::fake() with the Facade (Laravel app).
 */
final class ConductorFakeFacadeTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('conductor', ['base_url' => 'https://conductor.example/api']);
    }

    public function test_fake_swaps_client_then_assert_workflow_started(): void
    {
        Conductor::fake();

        Conductor::workflow()->start('order_processing', ['order_id' => 1]);

        Conductor::assertWorkflowStarted('order_processing');
        $this->addToAssertionCount(1);
    }

    public function test_fake_returns_conductor_fake_instance(): void
    {
        $fake = Conductor::fake();

        $this->assertSame($fake, Conductor::getFacadeRoot());
    }

    public function test_after_fake_tasks_poll_returns_null(): void
    {
        Conductor::fake();

        $result = Conductor::tasks()->poll('any_task');

        $this->assertNull($result);
    }
}
