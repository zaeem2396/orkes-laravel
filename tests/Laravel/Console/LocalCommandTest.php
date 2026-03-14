<?php

declare(strict_types=1);

namespace Conductor\Laravel\Tests\Console;

use Conductor\Laravel\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

final class LocalCommandTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('conductor', ['task_handlers' => []]);
    }

    public function test_conductor_local_exits_successfully_when_no_handlers(): void
    {
        $exitCode = Artisan::call('conductor:local', ['--once' => true]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('No task handlers registered', Artisan::output());
    }
}
