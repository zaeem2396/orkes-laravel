<?php

declare(strict_types=1);

namespace Conductor\Laravel\Console;

use Illuminate\Console\Command;

/**
 * Inspect Conductor: active workflows, failed workflows, pending tasks, workers.
 *
 * Example: php artisan conductor:inspect
 */
final class InspectCommand extends Command
{
    protected $signature = 'conductor:inspect';

    protected $description = 'Show active workflows, failed workflows, pending tasks, and workers';

    public function handle(): int
    {
        // TODO: Call SDK workflow/task APIs, display table.
        $this->info('Conductor inspect (stub)');

        return self::SUCCESS;
    }
}
