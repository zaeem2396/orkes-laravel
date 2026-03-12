<?php

declare(strict_types=1);

namespace Conductor\Laravel\Tests;

use Conductor\Laravel\Testing\ConductorFake;
use PHPUnit\Framework\TestCase;

final class ConductorFakeTest extends TestCase
{
    public function test_assert_workflow_started_passes_when_started(): void
    {
        $fake = new ConductorFake();
        $fake->workflow()->start('order_processing', ['order_id' => 1]);

        $fake->assertWorkflowStarted('order_processing');
        $this->addToAssertionCount(1);
    }

    public function test_assert_workflow_started_fails_when_not_started(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected workflow [other_workflow] to be started.');

        $fake = new ConductorFake();
        $fake->workflow()->start('order_processing', []);
        $fake->assertWorkflowStarted('other_workflow');
    }
}
