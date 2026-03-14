<?php

declare(strict_types=1);

namespace Conductor\Laravel\Console;

use Conductor\Client\ConductorClient;
use Illuminate\Console\Command;

/**
 * Start a Conductor workflow by name.
 *
 * Example: php artisan conductor:start order_processing
 */
final class StartWorkflowCommand extends Command
{
    protected $signature = 'conductor:start
                            {workflow : Workflow definition name}
                            {--input= : JSON input data}
                            {--correlation-id= : Correlation ID for the workflow}
                            {--version= : Workflow definition version}';

    protected $description = 'Start a Conductor workflow';

    public function handle(ConductorClient $client): int
    {
        $name = $this->argument('workflow');
        $input = $this->option('input') ? json_decode($this->option('input'), true) ?? [] : [];

        $correlationId = $this->option('correlation-id') ? (string) $this->option('correlation-id') : null;
        $version = $this->option('version') !== null && $this->option('version') !== ''
            ? (int) $this->option('version')
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
