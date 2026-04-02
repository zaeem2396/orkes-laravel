<?php

declare(strict_types=1);

namespace Conductor\Laravel\Tests\Console;

use Conductor\Client\ConductorClient;
use Conductor\Client\HttpClient;
use Conductor\Laravel\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Artisan;

final class StartWorkflowCommandTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('conductor', [
            'base_url' => 'https://conductor.example/api',
            'auth_token' => 'token',
            'auth_key' => null,
            'auth_secret' => null,
            'auth_header_style' => 'bearer',
            'timeout' => 30,
        ]);
    }

    public function test_conductor_start_returns_workflow_id(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{"workflowId":"test-wf-001"}'),
        ]);
        $http = new HttpClient(
            'http://localhost/api',
            null,
            5,
            new Client(['handler' => HandlerStack::create($mock)]),
        );
        $this->app->instance(ConductorClient::class, new ConductorClient($http));

        $exitCode = Artisan::call('conductor:start', ['workflow' => 'order_processing']);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('test-wf-001', Artisan::output());
    }

    public function test_conductor_start_with_input_passes_input(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{"workflowId":"wf-1"}'),
        ]);
        $http = new HttpClient(
            'http://localhost/api',
            null,
            5,
            new Client(['handler' => HandlerStack::create($mock)]),
        );
        $this->app->instance(ConductorClient::class, new ConductorClient($http));

        $exitCode = Artisan::call('conductor:start', [
            'workflow' => 'my_workflow',
            '--input' => '{"key":"value"}',
        ]);

        $this->assertSame(0, $exitCode);
    }

    public function test_conductor_start_fails_on_exception(): void
    {
        $mock = new MockHandler([
            new Response(500, [], 'Internal Server Error'),
        ]);
        $http = new HttpClient(
            'http://localhost/api',
            null,
            5,
            new Client(['handler' => HandlerStack::create($mock)]),
        );
        $this->app->instance(ConductorClient::class, new ConductorClient($http));

        $exitCode = Artisan::call('conductor:start', ['workflow' => 'order_processing']);

        $this->assertSame(1, $exitCode);
    }
}
