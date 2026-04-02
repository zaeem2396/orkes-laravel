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

    public function test_from_array_enables_http_retry_when_configured(): void
    {
        $client = ConductorClient::fromArray([
            'base_url' => 'http://localhost:8080/api',
            'retry_enabled' => true,
            'retry_max_attempts' => 4,
            'retry_initial_delay_ms' => 500,
        ]);

        $ref = new \ReflectionClass($client);
        $prop = $ref->getProperty('http');
        $prop->setAccessible(true);
        $http = $prop->getValue($client);
        $this->assertInstanceOf(HttpClient::class, $http);

        $httpRef = new \ReflectionClass($http);
        $retryProp = $httpRef->getProperty('retryHandler');
        $retryProp->setAccessible(true);

        $this->assertNotNull($retryProp->getValue($http));
    }

    public function test_from_array_uses_x_authorization_scheme_when_auth_header_style_orkes(): void
    {
        $client = ConductorClient::fromArray([
            'base_url' => 'http://localhost:8080/api',
            'token' => 'jwt',
            'auth_header_style' => 'orkes',
        ]);

        $ref = new \ReflectionClass($client);
        $prop = $ref->getProperty('http');
        $prop->setAccessible(true);
        $http = $prop->getValue($client);
        $this->assertInstanceOf(HttpClient::class, $http);

        $schemeProp = (new \ReflectionClass($http))->getProperty('authScheme');
        $schemeProp->setAccessible(true);
        $this->assertSame(HttpClient::AUTH_SCHEME_X_AUTHORIZATION, $schemeProp->getValue($http));
    }
}
