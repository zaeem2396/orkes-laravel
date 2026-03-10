<?php

declare(strict_types=1);

namespace Conductor\Tests\Client;

use Conductor\Client\HttpClient;
use Conductor\Exceptions\ConductorException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class HttpClientTest extends TestCase
{
    private function createClientWithHistory(MockHandler $mock, array &$container): Client
    {
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($container));

        return new Client(['handler' => $stack]);
    }

    public function test_request_returns_decoded_json(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{"id":"w1","status":"RUNNING"}'),
        ]);
        $container = [];
        $client = $this->createClientWithHistory($mock, $container);
        $http = new HttpClient('http://localhost:8080/api', null, 30, $client);

        $result = $http->request('GET', 'workflow/w1');

        $this->assertSame(['id' => 'w1', 'status' => 'RUNNING'], $result);
    }

    public function test_request_returns_empty_array_for_empty_body(): void
    {
        $mock = new MockHandler([
            new Response(204, [], ''),
        ]);
        $container = [];
        $client = $this->createClientWithHistory($mock, $container);
        $http = new HttpClient('http://localhost:8080/api', null, 30, $client);

        $result = $http->request('GET', 'workflow/w1');

        $this->assertSame([], $result);
    }

    public function test_request_adds_bearer_token_when_set(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{}'),
        ]);
        $container = [];
        $client = $this->createClientWithHistory($mock, $container);
        $http = new HttpClient('http://localhost:8080/api', 'secret-token', 30, $client);

        $http->request('GET', 'workflow/w1');

        $this->assertCount(1, $container);
        $request = $container[0]['request'];
        $this->assertSame('Bearer secret-token', $request->getHeaderLine('Authorization'));
    }

    public function test_request_appends_base_url_to_uri(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{}'),
        ]);
        $container = [];
        $client = $this->createClientWithHistory($mock, $container);
        $http = new HttpClient('http://localhost:8080/api', null, 30, $client);

        $http->request('GET', 'workflow/123');

        $this->assertCount(1, $container);
        $request = $container[0]['request'];
        $this->assertSame('http://localhost:8080/api/workflow/123', (string) $request->getUri());
    }

    public function test_request_throws_on_invalid_json(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'text/plain'], 'not json'),
        ]);
        $container = [];
        $client = $this->createClientWithHistory($mock, $container);
        $http = new HttpClient('http://localhost:8080/api', null, 30, $client);

        $this->expectException(ConductorException::class);
        $this->expectExceptionMessage('Invalid JSON');

        $http->request('GET', 'workflow/w1');
    }
}
