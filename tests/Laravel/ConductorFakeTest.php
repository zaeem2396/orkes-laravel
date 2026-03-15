<?php

declare(strict_types=1);

namespace Conductor\Laravel\Tests;

use Conductor\Laravel\Testing\ConductorFake;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ConductorFake with direct instantiation (no Laravel app). For Facade tests see ConductorFakeFacadeTest.
 */
final class ConductorFakeTest extends TestCase
{
    public function test_assert_workflow_started_passes_when_started(): void
    {
        $fake = new ConductorFake();
        $fake->workflow()->start('order_processing', ['order_id' => 1]);

        $fake->assertWorkflowStarted('order_processing');
        $this->addToAssertionCount(1); // assertWorkflowStarted does not throw
    }

    public function test_assert_workflow_started_fails_when_not_started(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected workflow [other_workflow] to be started.');

        $fake = new ConductorFake();
        $fake->workflow()->start('order_processing', []);
        $fake->assertWorkflowStarted('other_workflow');
    }

    public function test_assert_workflow_started_with_input_passes_when_matching(): void
    {
        $fake = new ConductorFake();
        $fake->workflow()->start('order_processing', ['order_id' => 123, 'customer_id' => 456]);

        $fake->assertWorkflowStartedWithInput('order_processing', ['order_id' => 123]);
        $fake->assertWorkflowStartedWithInput('order_processing', ['customer_id' => 456]);
        $this->addToAssertionCount(1);
    }

    public function test_assert_workflow_started_with_input_fails_when_input_mismatch(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected workflow [order_processing] to be started with input matching');

        $fake = new ConductorFake();
        $fake->workflow()->start('order_processing', ['order_id' => 123]);
        $fake->assertWorkflowStartedWithInput('order_processing', ['order_id' => 999]); // wrong value
    }

    public function test_assert_no_workflows_started_passes_when_none(): void
    {
        $fake = new ConductorFake();
        $fake->assertNoWorkflowsStarted();
        $this->addToAssertionCount(1);
    }

    public function test_assert_no_workflows_started_fails_when_any_started(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected no workflows to be started, but 1 were started.');

        $fake = new ConductorFake();
        $fake->workflow()->start('wf', []);
        $fake->assertNoWorkflowsStarted();
    }

    public function test_recorded_started_workflows_returns_list(): void
    {
        $fake = new ConductorFake();
        $this->assertSame([], $fake->recordedStartedWorkflows());

        $fake->workflow()->start('a', ['x' => 1]);
        $fake->workflow()->start('b', []);
        $recorded = $fake->recordedStartedWorkflows();
        $this->assertCount(2, $recorded);
        $first = array_shift($recorded);
        $second = array_shift($recorded);
        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertSame('a', $first['name']);
        $this->assertSame(['x' => 1], $first['input']);
        $this->assertSame('b', $second['name']);
    }

    public function test_assert_workflow_started_with_input_passes_for_subset(): void
    {
        $fake = new ConductorFake();
        $fake->workflow()->start('wf', ['a' => 1, 'b' => 2]);

        $fake->assertWorkflowStartedWithInput('wf', ['a' => 1]);
        $fake->assertWorkflowStartedWithInput('wf', ['b' => 2]);
        $fake->assertWorkflowStartedWithInput('wf', ['a' => 1, 'b' => 2]);
        $this->addToAssertionCount(1); // no exception
    }
}
