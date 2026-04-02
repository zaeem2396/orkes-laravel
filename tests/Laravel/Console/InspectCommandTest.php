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

final class InspectCommandTest extends TestCase
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
        ]);
    }

    public function test_conductor_inspect_displays_running_and_failed_counts(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'totalHits' => 1,
                'results' => [
                    ['workflowId' => 'wf-1', 'workflowType' => 'order_processing', 'status' => 'RUNNING', 'startTime' => '2025-01-01T00:00:00Z'],
                ],
            ])),
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'totalHits' => 0,
                'results' => [],
            ])),
        ]);
        $http = new HttpClient(
            'http://localhost/api',
            null,
            5,
            new Client(['handler' => HandlerStack::create($mock)]),
        );
        $this->app->instance(ConductorClient::class, new ConductorClient($http));

        $exitCode = Artisan::call('conductor:inspect', ['--size' => 10]);

        $this->assertSame(0, $exitCode);
        $output = Artisan::output();
        $this->assertStringContainsString('Running', $output);
        $this->assertStringContainsString('Failed', $output);
        $this->assertStringContainsString('wf-1', $output);
    }
}
