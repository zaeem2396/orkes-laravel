<?php

declare(strict_types=1);

namespace Conductor\Laravel\Tests;

use Conductor\Client\ConductorClient;
use Conductor\Laravel\Facades\Conductor;
use Illuminate\Support\Facades\Artisan;

final class ConductorServiceProviderTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('conductor', [
            'base_url' => 'https://conductor.example/api',
            'auth_token' => 'test-token',
            'timeout' => 60,
            'worker_concurrency' => 3,
            'poll_interval' => 10,
            'retry_enabled' => false,
        ]);
    }

    public function test_conductor_client_is_registered_as_singleton(): void
    {
        $client1 = $this->app->make(ConductorClient::class);
        $client2 = $this->app->make(ConductorClient::class);

        $this->assertInstanceOf(ConductorClient::class, $client1);
        $this->assertSame($client1, $client2);
    }

    public function test_facade_resolves_to_conductor_client(): void
    {
        $client = Conductor::getFacadeRoot();

        $this->assertInstanceOf(ConductorClient::class, $client);
    }

    public function test_facade_workflow_returns_workflow_client(): void
    {
        $workflow = Conductor::workflow();

        $this->assertSame($workflow, Conductor::workflow());
    }

    public function test_facade_tasks_returns_task_client(): void
    {
        $tasks = Conductor::tasks();

        $this->assertSame($tasks, Conductor::tasks());
    }

    public function test_facade_workers_returns_worker_instance(): void
    {
        $this->assertInstanceOf(\Conductor\Task\Worker::class, Conductor::workers());
    }

    public function test_config_is_merged(): void
    {
        $config = $this->app['config']->get('conductor');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('base_url', $config);
        $this->assertArrayHasKey('auth_token', $config);
        $this->assertArrayHasKey('timeout', $config);
        $this->assertArrayHasKey('worker_concurrency', $config);
        $this->assertArrayHasKey('poll_interval', $config);
    }

    public function test_config_values_from_environment(): void
    {
        $config = $this->app['config']->get('conductor');

        $this->assertSame('https://conductor.example/api', $config['base_url']);
        $this->assertSame('test-token', $config['auth_token']);
        $this->assertSame(60, $config['timeout']);
        $this->assertSame(3, $config['worker_concurrency']);
        $this->assertSame(10, $config['poll_interval']);
    }

    public function test_commands_are_registered(): void
    {
        $commands = Artisan::all();

        $this->assertArrayHasKey('conductor:start', $commands);
        $this->assertArrayHasKey('conductor:work', $commands);
        $this->assertArrayHasKey('conductor:inspect', $commands);
    }

    public function test_conductor_client_works_with_retry_enabled(): void
    {
        $this->app['config']->set('conductor.retry_enabled', true);
        $this->app['config']->set('conductor.retry_max_attempts', 2);
        $this->app['config']->set('conductor.retry_initial_delay_ms', 10);

        $client = $this->app->make(ConductorClient::class);

        $this->assertInstanceOf(ConductorClient::class, $client);
    }

    public function test_config_includes_retry_keys_when_merged(): void
    {
        $config = $this->app['config']->get('conductor');

        $this->assertArrayHasKey('retry_enabled', $config);
        $this->assertArrayHasKey('retry_max_attempts', $config);
        $this->assertArrayHasKey('retry_initial_delay_ms', $config);
    }

    public function test_config_timeout_is_integer(): void
    {
        $config = $this->app['config']->get('conductor');

        $this->assertIsInt($config['timeout']);
    }
}
