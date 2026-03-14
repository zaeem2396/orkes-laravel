<?php

declare(strict_types=1);

namespace Conductor\Laravel\Console;

use Conductor\Client\ConductorClient;
use Illuminate\Console\Command;

/**
 * Start a Conductor workflow by name.
 *
 * Options: workflow (name), --input (JSON), --correlation-id, --wf-version.
 * Example: php artisan conductor:start order_processing
 * Example: php artisan conductor:start order_processing --input='{"order_id":123}' --correlation-id=ord-1 --wf-version=2
 */
final class StartWorkflowCommand extends Command
{
    protected $signature = 'conductor:start
                            {workflow : Workflow definition name}
                            {--input= : JSON input data}
                            {--correlation-id= : Correlation ID for the workflow}
                            {--wf-version= : Workflow definition version}';

    protected $description = 'Start a Conductor workflow by name and output workflow ID';

    public function handle(ConductorClient $client): int
    {
        $name = $this->argument('workflow');
        $input = $this->option('input') ? json_decode($this->option('input'), true) ?? [] : [];

        $correlationId = $this->option('correlation-id') ? (string) $this->option('correlation-id') : null;
        $version = $this->option('wf-version') !== null && $this->option('wf-version') !== ''
            ? (int) $this->option('wf-version')
            : null;

        try {
            $workflowId = $client->workflow()->start(
                $name,
                is_array($input) ? $input : [],
                $correlationId,
                $version,
            );
            $this->info("Workflow started: {$workflowId}");

            return self::SUCCESS;
        } catch (\Conductor\Exceptions\ConductorException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
