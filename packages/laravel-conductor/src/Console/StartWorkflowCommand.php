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
                            {--input= : JSON input data}';

    protected $description = 'Start a Conductor workflow';

    public function handle(ConductorClient $client): int
    {
        $name = $this->argument('workflow');
        $input = $this->option('input') ? json_decode($this->option('input'), true) ?? [] : [];

        // TODO: Start workflow, output workflow ID.
        $this->info("Starting workflow: {$name}");

        return self::SUCCESS;
    }
}
