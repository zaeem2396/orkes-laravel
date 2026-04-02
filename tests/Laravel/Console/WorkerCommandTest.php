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
            'auth_token' => null,
            'auth_key' => null,
            'auth_secret' => null,
            'auth_header_style' => 'bearer',
            'timeout' => 30,
            'task_handlers' => [],
        ]);
    }

    public function test_conductor_work_exits_successfully_when_no_handlers(): void
    {
        $exitCode = Artisan::call('conductor:work');

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('No task handlers registered', Artisan::output());
    }

    public function test_conductor_work_once_exits_without_infinite_loop(): void
    {
        $exitCode = Artisan::call('conductor:work', ['--once' => true]);

        $this->assertSame(0, $exitCode);
    }
}
