<?php

declare(strict_types=1);

namespace Conductor\Tests\Task;

use Conductor\Client\HttpClient;
use Conductor\Task\TaskClient;
use Conductor\Task\Worker;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class WorkerTest extends TestCase
{
    private function createClientWithHistory(MockHandler $mock, array &$container): TaskClient
    {
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($container));
        $http = new HttpClient('http://localhost:8080/api', null, 30, new Client(['handler' => $stack]));

        return new TaskClient($http);
    }

    public function test_listen_registers_handler_and_returns_self(): void
    {
        $mock = new MockHandler([new Response(204, [], '')]);
        $container = [];
        $client = $this->createClientWithHistory($mock, $container);
        $worker = new Worker($client, 1);

        $self = $worker->listen('my_task', fn (array $t) => ['status' => 'COMPLETED', 'outputData' => []]);

        $this->assertSame($worker, $self);
    }

    public function test_run_one_cycle_polls_and_completes_task(): void
    {
        $taskPayload = ['taskId' => 't1', 'workflowInstanceId' => 'w1', 'taskType' => 'process_payment'];
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($taskPayload)),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
        ]);
        $container = [];
        $client = $this->createClientWithHistory($mock, $container);
        $worker = new Worker($client, 5);
        $worker->listen('process_payment', function (array $task) {
            $this->assertSame('t1', $task['taskId']);

            return ['status' => 'COMPLETED', 'outputData' => ['done' => true]];
        });

        $worker->runOneCycle();

        $this->assertCount(3, $container);
        $this->assertStringContainsString('tasks/poll/process_payment', (string) $container[0]['request']->getUri());
        $ackBody = json_decode((string) $container[1]['request']->getBody(), true);
        $this->assertSame('IN_PROGRESS', $ackBody['status']);
        $completeBody = json_decode((string) $container[2]['request']->getBody(), true);
        $this->assertSame('COMPLETED', $completeBody['status']);
        $this->assertSame(['done' => true], $completeBody['outputData']);
    }

    public function test_run_one_cycle_fails_task_when_handler_returns_failed(): void
    {
        $taskPayload = ['taskId' => 't2', 'workflowInstanceId' => 'w2', 'taskType' => 'validate'];
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($taskPayload)),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
        ]);
        $container = [];
        $client = $this->createClientWithHistory($mock, $container);
        $worker = new Worker($client, 5);
        $worker->listen('validate', fn () => [
            'status' => 'FAILED',
            'reasonForIncompletion' => 'Validation error',
            'outputData' => ['errors' => ['invalid']],
        ]);

        $worker->runOneCycle();

        $failBody = json_decode((string) $container[2]['request']->getBody(), true);
        $this->assertSame('FAILED', $failBody['status']);
        $this->assertSame('Validation error', $failBody['reasonForIncompletion']);
        $this->assertSame(['errors' => ['invalid']], $failBody['outputData']);
    }

    public function test_run_one_cycle_fails_task_on_handler_exception(): void
    {
        $taskPayload = ['taskId' => 't3', 'workflowInstanceId' => null, 'taskType' => 'boom'];
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($taskPayload)),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
        ]);
        $container = [];
        $client = $this->createClientWithHistory($mock, $container);
        $worker = new Worker($client, 5, null, null, 0);
        $worker->listen('boom', function (): array {
            throw new \RuntimeException('Something broke');
        });

        $worker->runOneCycle();

        $failBody = json_decode((string) $container[2]['request']->getBody(), true);
        $this->assertSame('FAILED', $failBody['status']);
        $this->assertStringContainsString('Something broke', $failBody['reasonForIncompletion']);
    }

    public function test_run_one_cycle_retries_then_fails_when_max_retries_set(): void
    {
        $taskPayload = ['taskId' => 't4', 'workflowInstanceId' => 'w4', 'taskType' => 'flaky'];
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($taskPayload)),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
        ]);
        $container = [];
        $client = $this->createClientWithHistory($mock, $container);
        $attempts = 0;
        $worker = new Worker($client, 5, null, null, 1);
        $worker->listen('flaky', function () use (&$attempts): array {
            $attempts++;
            if ($attempts < 2) {
                throw new \RuntimeException('First attempt');
            }

            return ['status' => 'COMPLETED', 'outputData' => []];
        });

        $worker->runOneCycle();

        $this->assertSame(2, $attempts);
        $completeBody = json_decode((string) $container[2]['request']->getBody(), true);
        $this->assertSame('COMPLETED', $completeBody['status']);
    }

    public function test_run_one_cycle_does_nothing_when_no_task_available(): void
    {
        $mock = new MockHandler([
            new Response(204, [], ''),
        ]);
        $container = [];
        $client = $this->createClientWithHistory($mock, $container);
        $worker = new Worker($client, 5);
        $worker->listen('idle', fn () => ['status' => 'COMPLETED', 'outputData' => []]);

        $worker->runOneCycle();

        $this->assertCount(1, $container);
    }

    public function test_run_one_cycle_passes_worker_id_and_domain_to_poll(): void
    {
        $mock = new MockHandler([
            new Response(204, [], ''),
        ]);
        $container = [];
        $client = $this->createClientWithHistory($mock, $container);
        $worker = new Worker($client, 5, 'worker-1', 'domain-a');
        $worker->listen('my_type', fn () => ['status' => 'COMPLETED', 'outputData' => []]);

        $worker->runOneCycle();

        $uri = (string) $container[0]['request']->getUri();
        $this->assertStringContainsString('workerid=worker-1', $uri);
        $this->assertStringContainsString('domain=domain-a', $uri);
    }

    public function test_run_one_cycle_fails_task_when_handler_returns_invalid_status(): void
    {
        $taskPayload = ['taskId' => 't5', 'workflowInstanceId' => 'w5', 'taskType' => 'weird'];
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($taskPayload)),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
        ]);
        $container = [];
        $client = $this->createClientWithHistory($mock, $container);
        $worker = new Worker($client, 5);
        $worker->listen('weird', fn () => ['status' => 'UNKNOWN', 'outputData' => []]);

        $worker->runOneCycle();

        $failBody = json_decode((string) $container[2]['request']->getBody(), true);
        $this->assertSame('FAILED', $failBody['status']);
        $this->assertStringContainsString('Invalid handler status', $failBody['reasonForIncompletion']);
    }
}
