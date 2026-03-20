<?php

declare(strict_types=1);

namespace Conductor\Laravel\Console;

use Conductor\Client\ConductorClient;
use Conductor\Laravel\Workers\TaskHandler;
use Conductor\Task\Worker;
use Illuminate\Console\Command;

/**
 * Local development: run workers with optional single cycle (--once).
 *
 * Example: php artisan conductor:local
 * Example: php artisan conductor:local --once
 */
final class LocalCommand extends Command
{
    protected $signature = 'conductor:local
                            {--once : Run one poll cycle then exit}
                            {--task= : Task type to poll}
                            {--queue= : Queue/domain name}';

    protected $description = 'Local dev: run workers; use --once to run one poll cycle then exit';

    public function handle(ConductorClient $client): int
    {
        $config = config('conductor', []);
        $pollInterval = (int) ($config['poll_interval'] ?? 5);
        $maxRetries = (int) ($config['worker_max_retries'] ?? 0);
        $taskHandlers = $config['task_handlers'] ?? [];
        $taskFilter = $this->option('task') ? (string) $this->option('task') : null;
        $domain = $this->option('queue') ? (string) $this->option('queue') : null;

        $worker = new Worker(
            $client->tasks(),
            $pollInterval,
            null,
            $domain,
            $maxRetries,
        );

        $registered = 0;
        foreach ($taskHandlers as $class) {
            if (! is_string($class)) {
                continue;
            }
            $handler = $this->laravel->make($class);
            if (! $handler instanceof TaskHandler) {
                continue;
            }
            $taskType = $handler->taskType();
            if ($taskFilter !== null && $taskFilter !== '' && $taskType !== $taskFilter) {
                continue;
            }
            $worker->listen($taskType, function (array $task) use ($handler): array {
                $output = $handler->handle($task);
                if (isset($output['status'])) {
                    return $output;
                }

                return ['status' => 'COMPLETED', 'outputData' => $output];
            });
            $registered++;
        }

        if ($registered === 0) {
            $this->warn('No task handlers registered. Add class names to config/conductor.php task_handlers.');

            return self::SUCCESS;
        }

        if ($this->option('once')) {
            $worker->runOneCycle();

            return self::SUCCESS;
        }

        $this->info('Local worker started. Press Ctrl+C to stop.');
        $worker->run();

        return self::SUCCESS;
    }
}
