<?php

declare(strict_types=1);

namespace Conductor\Laravel\Tests\DSL;

use Conductor\Client\HttpClient;
use Conductor\Laravel\DSL\Workflow;
use Conductor\Laravel\DSL\WorkflowDefinition;
use Conductor\Workflow\WorkflowClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class WorkflowDefinitionTest extends TestCase
{
    private function createHttpWithHistory(MockHandler $mock, array &$container): HttpClient
    {
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($container));

        return new HttpClient('http://localhost/api', null, 5, new Client(['handler' => $stack]));
    }
    public function test_define_returns_workflow_definition(): void
    {
        $def = Workflow::define('order_processing');

        $this->assertInstanceOf(WorkflowDefinition::class, $def);
    }

    public function test_to_array_with_no_tasks_returns_empty_tasks(): void
    {
        $def = Workflow::define('empty_workflow');
        $arr = $def->toArray();

        $this->assertSame('empty_workflow', $arr['name']);
        $this->assertSame([], $arr['tasks']);
    }

    public function test_task_chaining_adds_tasks(): void
    {
        $def = Workflow::define('order_processing')
            ->task('validate_order')
            ->task('charge_payment')
            ->task('send_confirmation');

        $arr = $def->toArray();

        $this->assertSame('order_processing', $arr['name']);
        $this->assertCount(3, $arr['tasks']);
        $this->assertSame('validate_order', $arr['tasks'][0]['name']);
        $this->assertSame('validate_order_ref', $arr['tasks'][0]['taskReferenceName']);
        $this->assertSame('SIMPLE', $arr['tasks'][0]['type']);
        $this->assertSame('charge_payment', $arr['tasks'][1]['name']);
        $this->assertSame('send_confirmation', $arr['tasks'][2]['name']);
    }

    public function test_to_array_includes_schema_version_and_owner(): void
    {
        $def = Workflow::define('my_workflow')->task('step1');
        $arr = $def->toArray();

        $this->assertSame(2, $arr['schemaVersion']);
        $this->assertSame('conductor@example.com', $arr['ownerEmail']);
        $this->assertSame(1, $arr['version']);
    }

    public function test_description_and_version_and_owner_email_are_used(): void
    {
        $def = Workflow::define('wf')
            ->description('My workflow')
            ->version(3)
            ->ownerEmail('team@example.com')
            ->task('t1');
        $arr = $def->toArray();

        $this->assertSame('My workflow', $arr['description']);
        $this->assertSame(3, $arr['version']);
        $this->assertSame('team@example.com', $arr['ownerEmail']);
    }

    public function test_input_parameters_included_in_to_array(): void
    {
        $def = Workflow::define('wf')->inputParameters(['orderId', 'amount'])->task('t1');
        $arr = $def->toArray();

        $this->assertSame(['orderId', 'amount'], $arr['inputParameters']);
    }

    public function test_to_json_returns_valid_json(): void
    {
        $def = Workflow::define('order_processing')->task('validate_order');
        $json = $def->toJson();

        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertSame('order_processing', $decoded['name']);
        $this->assertCount(1, $decoded['tasks']);
    }

    public function test_register_calls_workflow_client(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{}'),
        ]);
        $container = [];
        $http = $this->createHttpWithHistory($mock, $container);
        $workflowClient = new WorkflowClient($http);
        $def = Workflow::define('test_wf')->task('t1');

        $def->register($workflowClient);

        $this->assertCount(1, $container);
        $request = $container[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringContainsString('metadata/workflow', (string) $request->getUri());
        $body = json_decode((string) $request->getBody(), true);
        $this->assertSame('test_wf', $body['name']);
        $this->assertCount(1, $body['tasks']);
    }
}
