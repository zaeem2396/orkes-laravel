<?php

declare(strict_types=1);

namespace Conductor\Tests\Task;

use Conductor\Client\HttpClient;
use Conductor\Exceptions\TaskException;
use Conductor\Task\TaskClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class TaskClientTest extends TestCase
{
    private function createClientWithHistory(MockHandler $mock, array &$container): HttpClient
    {
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($container));

        return new HttpClient('http://localhost:8080/api', null, 30, new Client(['handler' => $stack]));
    }

    public function test_poll_returns_null_when_no_task(): void
    {
        $mock = new MockHandler([
            new Response(204, [], ''),
        ]);
        $container = [];
        $http = $this->createClientWithHistory($mock, $container);
        $client = new TaskClient($http);

        $task = $client->poll('process_payment');

        $this->assertNull($task);
        $this->assertStringContainsString('tasks/poll/process_payment', (string) $container[0]['request']->getUri());
    }

    public function test_poll_returns_task_when_available(): void
    {
        $taskPayload = ['taskId' => 't1', 'workflowInstanceId' => 'w1', 'taskType' => 'process_payment', 'status' => 'IN_PROGRESS'];
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($taskPayload)),
        ]);
        $container = [];
        $http = $this->createClientWithHistory($mock, $container);
        $client = new TaskClient($http);

        $task = $client->poll('process_payment');

        $this->assertIsArray($task);
        $this->assertSame('t1', $task['taskId']);
        $this->assertSame('w1', $task['workflowInstanceId']);
    }

    public function test_poll_passes_worker_id_and_domain(): void
    {
        $mock = new MockHandler([
            new Response(204, [], ''),
        ]);
        $container = [];
        $http = $this->createClientWithHistory($mock, $container);
        $client = new TaskClient($http);

        $client->poll('my_task', 'worker-1', 'domain-a');

        $uri = (string) $container[0]['request']->getUri();
        $this->assertStringContainsString('workerid=worker-1', $uri);
        $this->assertStringContainsString('domain=domain-a', $uri);
    }

    public function test_complete_posts_to_tasks_with_status_and_output(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{}'),
        ]);
        $container = [];
        $http = $this->createClientWithHistory($mock, $container);
        $client = new TaskClient($http);

        $client->complete('t1', ['payment_id' => 'p123'], 'w1');

        $body = json_decode((string) $container[0]['request']->getBody(), true);
        $this->assertSame('t1', $body['taskId']);
        $this->assertSame('COMPLETED', $body['status']);
        $this->assertSame(['payment_id' => 'p123'], $body['outputData']);
        $this->assertSame('w1', $body['workflowInstanceId']);
        $this->assertSame('POST', $container[0]['request']->getMethod());
        $this->assertStringContainsString('tasks', (string) $container[0]['request']->getUri());
    }

    public function test_fail_posts_with_reason_and_status_failed(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{}'),
        ]);
        $container = [];
        $http = $this->createClientWithHistory($mock, $container);
        $client = new TaskClient($http);

        $client->fail('t1', 'Payment declined', ['code' => 'DECLINED']);

        $body = json_decode((string) $container[0]['request']->getBody(), true);
        $this->assertSame('FAILED', $body['status']);
        $this->assertSame('Payment declined', $body['reasonForIncompletion']);
        $this->assertSame(['code' => 'DECLINED'], $body['outputData']);
    }

    public function test_update_posts_with_status_and_optional_callback(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{}'),
        ]);
        $container = [];
        $http = $this->createClientWithHistory($mock, $container);
        $client = new TaskClient($http);

        $client->update('t1', 'IN_PROGRESS', ['progress' => 50], null, 60, 'w1');

        $body = json_decode((string) $container[0]['request']->getBody(), true);
        $this->assertSame('IN_PROGRESS', $body['status']);
        $this->assertSame(['progress' => 50], $body['outputData']);
        $this->assertSame(60, $body['callbackAfterSeconds']);
    }

    public function test_ack_posts_in_progress(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{}'),
        ]);
        $container = [];
        $http = $this->createClientWithHistory($mock, $container);
        $client = new TaskClient($http);

        $client->ack('t1', 'w1');

        $body = json_decode((string) $container[0]['request']->getBody(), true);
        $this->assertSame('t1', $body['taskId']);
        $this->assertSame('IN_PROGRESS', $body['status']);
        $this->assertSame([], $body['outputData']);
    }

    public function test_http_failure_throws_task_exception(): void
    {
        $mock = new MockHandler([
            new Response(500, [], 'Internal Server Error'),
        ]);
        $container = [];
        $http = $this->createClientWithHistory($mock, $container);
        $client = new TaskClient($http);

        $this->expectException(TaskException::class);
        $this->expectExceptionMessage('Task update failed');

        $client->complete('t1', []);
    }
}
