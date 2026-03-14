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

final class FailuresCommandTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('conductor', ['base_url' => 'https://conductor.example/api']);
    }

    public function test_conductor_failures_reports_no_failed_workflows(): void
    {
        $mock = new MockHandler([
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

        $exitCode = Artisan::call('conductor:failures');

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('No failed workflows', Artisan::output());
    }
}
