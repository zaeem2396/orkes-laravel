<?php

declare(strict_types=1);

namespace Conductor\Tests\Workflow;

use Conductor\Client\HttpClient;
use Conductor\Workflow\WorkflowClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class WorkflowClientTest extends TestCase
{
    private function createClientWithHistory(MockHandler $mock, array &$container): HttpClient
    {
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($container));

        return new HttpClient('http://localhost:8080/api', null, 30, new Client(['handler' => $stack]));
    }

    public function test_start_returns_workflow_id_from_array(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{"workflowId":"wf-123"}'),
        ]);
        $container = [];
        $http = $this->createClientWithHistory($mock, $container);
        $client = new WorkflowClient($http);

        $id = $client->start('order_processing', ['order_id' => 1]);

        $this->assertSame('wf-123', $id);
        $this->assertCount(1, $container);
        $request = $container[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringContainsString('/workflow/order_processing', (string) $request->getUri());
        $body = (string) $request->getBody();
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame(['order_id' => 1], $decoded);
    }

    public function test_start_throws_when_response_lacks_workflow_id(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{}'),
        ]);
        $container = [];
        $http = $this->createClientWithHistory($mock, $container);
        $client = new WorkflowClient($http);

        $this->expectException(\Conductor\Exceptions\WorkflowException::class);
        $this->expectExceptionMessage('Start workflow did not return a workflow ID');

        $client->start('order_processing', []);
    }

    public function test_start_includes_correlation_id_and_version_when_provided(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{"workflowId":"wf-456"}'),
        ]);
        $container = [];
        $http = $this->createClientWithHistory($mock, $container);
        $client = new WorkflowClient($http);

        $client->start('my_workflow', [], 'corr-1', 2);

        $uri = (string) $container[0]['request']->getUri();
        $this->assertStringContainsString('correlationId=corr-1', $uri);
        $this->assertStringContainsString('version=2', $uri);
    }

    public function test_get_workflow_calls_get_with_include_tasks(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{"workflowId":"wf-1","status":"RUNNING"}'),
        ]);
        $container = [];
        $http = $this->createClientWithHistory($mock, $container);
        $client = new WorkflowClient($http);

        $result = $client->getWorkflow('wf-1', true);

        $this->assertSame(['workflowId' => 'wf-1', 'status' => 'RUNNING'], $result);
        $request = $container[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertStringContainsString('workflow/wf-1', (string) $request->getUri());
        $this->assertStringContainsString('includeTasks=true', (string) $request->getUri());
    }

    public function test_terminate_workflow_calls_delete(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{}'),
        ]);
        $container = [];
        $http = $this->createClientWithHistory($mock, $container);
        $client = new WorkflowClient($http);

        $client->terminateWorkflow('wf-1');

        $this->assertSame('DELETE', $container[0]['request']->getMethod());
        $this->assertStringContainsString('workflow/wf-1', (string) $container[0]['request']->getUri());
    }

    public function test_retry_pause_resume_call_correct_endpoints(): void
    {
        $mock = new MockHandler([
            new Response(200, [], ''),
            new Response(200, [], ''),
            new Response(200, [], ''),
        ]);
        $container = [];
        $http = $this->createClientWithHistory($mock, $container);
        $client = new WorkflowClient($http);

        $client->retryWorkflow('wf-1');
        $this->assertStringContainsString('/workflow/wf-1/retry', (string) $container[0]['request']->getUri());
        $this->assertSame('POST', $container[0]['request']->getMethod());

        $client->pauseWorkflow('wf-1');
        $this->assertStringContainsString('/workflow/wf-1/pause', (string) $container[1]['request']->getUri());
        $this->assertSame('PUT', $container[1]['request']->getMethod());

        $client->resumeWorkflow('wf-1');
        $this->assertStringContainsString('/workflow/wf-1/resume', (string) $container[2]['request']->getUri());
        $this->assertSame('PUT', $container[2]['request']->getMethod());
    }

    public function test_get_workflow_status_returns_get_workflow_without_tasks(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{"workflowId":"wf-1","status":"COMPLETED"}'),
        ]);
        $container = [];
        $http = $this->createClientWithHistory($mock, $container);
        $client = new WorkflowClient($http);

        $result = $client->getWorkflowStatus('wf-1');

        $this->assertStringContainsString('includeTasks=false', (string) $container[0]['request']->getUri());
        $this->assertSame('COMPLETED', $result['status']);
    }

    public function test_register_workflow_definition_posts_to_metadata(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{}'),
        ]);
        $container = [];
        $http = $this->createClientWithHistory($mock, $container);
        $client = new WorkflowClient($http);
        $def = ['name' => 'test_wf', 'tasks' => [], 'version' => 1];

        $client->registerWorkflowDefinition($def);

        $request = $container[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringContainsString('metadata/workflow', (string) $request->getUri());
        $this->assertSame($def, json_decode((string) $request->getBody(), true));
    }

    public function test_update_workflow_definition_puts_list_to_metadata(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{}'),
        ]);
        $container = [];
        $http = $this->createClientWithHistory($mock, $container);
        $client = new WorkflowClient($http);
        $defs = [['name' => 'wf1', 'version' => 1], ['name' => 'wf2', 'version' => 1]];

        $client->updateWorkflowDefinition($defs);

        $request = $container[0]['request'];
        $this->assertSame('PUT', $request->getMethod());
        $this->assertStringContainsString('metadata/workflow', (string) $request->getUri());
        $this->assertSame($defs, json_decode((string) $request->getBody(), true));
    }
}
