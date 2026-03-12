<?php

declare(strict_types=1);

namespace Conductor\Tests\Client;

use Conductor\Client\ConductorClient;
use Conductor\Client\HttpClient;
use PHPUnit\Framework\TestCase;

final class ConductorClientTest extends TestCase
{
    public function test_workflow_returns_workflow_client(): void
    {
        $http = new HttpClient('http://localhost:8080/api', null, 30);
        $client = new ConductorClient($http);

        $this->assertSame($client->workflow(), $client->workflow());
    }

    public function test_tasks_returns_task_client(): void
    {
        $http = new HttpClient('http://localhost:8080/api', null, 30);
        $client = new ConductorClient($http);

        $this->assertSame($client->tasks(), $client->tasks());
    }

    public function test_from_array_creates_client_with_http(): void
    {
        $client = ConductorClient::fromArray([
            'base_url' => 'http://conductor.example/api',
            'token' => 'xyz',
            'timeout' => 10,
        ]);

        $this->assertInstanceOf(ConductorClient::class, $client);
        $this->assertInstanceOf(\Conductor\Workflow\WorkflowClient::class, $client->workflow());
    }

    public function test_from_array_accepts_null_token(): void
    {
        $client = ConductorClient::fromArray([
            'base_url' => 'http://localhost:8080/api',
        ]);

        $this->assertInstanceOf(ConductorClient::class, $client);
    }
}
