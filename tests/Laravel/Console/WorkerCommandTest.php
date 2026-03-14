<?php

declare(strict_types=1);

namespace Conductor\Laravel\Tests\Console;

use Conductor\Laravel\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

final class WorkerCommandTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('conductor', [
            'base_url' => 'https://conductor.example/api',
            'task_handlers' => [],
        ]);
    }

    public function test_conductor_work_exits_successfully_when_no_handlers(): void
    {
        $exitCode = Artisan::call('conductor:work');

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('No task handlers registered', Artisan::output());
    }
}
