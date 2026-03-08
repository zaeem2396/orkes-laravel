<?php

declare(strict_types=1);

namespace Conductor\Laravel\Console;

use Illuminate\Console\Command;

/**
 * Run Conductor task workers.
 *
 * Options: --task, --concurrency, --queue
 * Example: php artisan conductor:work --task=process_payment --concurrency=5
 */
final class WorkerCommand extends Command
{
    protected $signature = 'conductor:work
                            {--task= : Task type to poll}
                            {--concurrency=5 : Number of concurrent workers}
                            {--queue= : Queue name}';

    protected $description = 'Run Conductor task workers';

    public function handle(): int
    {
        // TODO: Resolve worker from registered handlers, run loop.
        $this->info('Conductor worker (stub)');

        return self::SUCCESS;
    }
}
