<?php

declare(strict_types=1);

namespace Conductor\Tests\Client;

use Conductor\Client\HttpClient;
use Conductor\Exceptions\AuthenticationException;
use Conductor\Exceptions\ConductorException;
use Conductor\Exceptions\RetryableException;
use Conductor\Retry\ExponentialDelayStrategy;
use Conductor\Retry\RetryHandler;
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

    public function test_request_maps_plain_uuid_body_to_workflow_id_on_success(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'text/plain'], "59e73499-23c5-11f1-9a8f-3ad7b569bd26\n"),
        ]);
        $container = [];
        $client = $this->createClientWithHistory($mock, $container);
        $http = new HttpClient('http://localhost:8080/api', null, 30, $client);

        $result = $http->request('POST', 'workflow', ['name' => 'demo', 'input' => []]);

        $this->assertSame(['workflowId' => '59e73499-23c5-11f1-9a8f-3ad7b569bd26'], $result);
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

    public function test_request_throws_on_json_encode_failure(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{}'),
        ]);
        $container = [];
        $client = $this->createClientWithHistory($mock, $container);
        $http = new HttpClient('http://localhost:8080/api', null, 30, $client);

        $circular = [];
        $circular['self'] = &$circular;

        $this->expectException(ConductorException::class);
        $this->expectExceptionMessage('Failed to encode request body as JSON');

        $http->request('POST', 'workflow', $circular);
    }

    public function test_request_throws_authentication_exception_on_401(): void
    {
        $mock = new MockHandler([
            new Response(401, ['Content-Type' => 'application/json'], '{"message":"Unauthorized"}'),
        ]);
        $container = [];
        $client = $this->createClientWithHistory($mock, $container);
        $http = new HttpClient('http://localhost:8080/api', 'bad-token', 30, $client);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('401');

        $http->request('GET', 'workflow/w1');
    }

    public function test_request_retries_on_500_when_retry_handler_set(): void
    {
        $mock = new MockHandler([
            new Response(500, [], 'Internal Server Error'),
            new Response(500, [], 'Internal Server Error'),
            new Response(200, ['Content-Type' => 'application/json'], '{"ok":true}'),
        ]);
        $container = [];
        $client = $this->createClientWithHistory($mock, $container);
        $retryHandler = new RetryHandler(3, new ExponentialDelayStrategy(1, 2.0));
        $http = new HttpClient('http://localhost:8080/api', null, 30, $client, $retryHandler);

        $result = $http->request('GET', 'workflow/w1');

        $this->assertSame(['ok' => true], $result);
        $this->assertCount(3, $container);
    }

    public function test_request_throws_retryable_after_exhausting_retries_on_500(): void
    {
        $mock = new MockHandler([
            new Response(500, [], 'Error'),
            new Response(500, [], 'Error'),
            new Response(500, [], 'Error'),
        ]);
        $container = [];
        $client = $this->createClientWithHistory($mock, $container);
        $retryHandler = new RetryHandler(3, new ExponentialDelayStrategy(1, 2.0));
        $http = new HttpClient('http://localhost:8080/api', null, 30, $client, $retryHandler);

        $this->expectException(RetryableException::class);
        $this->expectExceptionMessage('500');

        $http->request('GET', 'workflow/w1');
    }
}
