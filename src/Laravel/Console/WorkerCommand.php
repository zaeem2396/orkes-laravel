<?php

declare(strict_types=1);

namespace Conductor\Laravel\Console;

use Conductor\Client\ConductorClient;
use Conductor\Laravel\Workers\TaskHandler;
use Conductor\Task\Worker;
use Illuminate\Console\Command;

/**
 * Run Conductor task workers.
 *
 * Options: --task (filter task type), --queue (domain), --concurrency (reserved).
 * Example: php artisan conductor:work --task=process_payment --queue=my-queue
 */
final class WorkerCommand extends Command
{
    protected $signature = 'conductor:work
                            {--task= : Task type to poll (default: all registered)}
                            {--concurrency= : Number of concurrent workers (reserved)}
                            {--queue= : Queue/domain name}';

    protected $description = 'Run Conductor task workers (uses config task_handlers, --task filter, --queue domain)';

    public function handle(ConductorClient $client): int
    {
        $config = config('conductor', []);
        $pollInterval = (int) ($config['poll_interval'] ?? 5);
        $taskHandlers = $config['task_handlers'] ?? [];
        $taskFilter = $this->option('task') ? (string) $this->option('task') : null;
        $domain = $this->option('queue') ? (string) $this->option('queue') : null;

        $worker = new Worker(
            $client->tasks(),
            $pollInterval,
            null,
            $domain,
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

                return ['status' => 'COMPLETED', 'outputData' => $output];
            });
            $registered++;
        }

        if ($registered === 0) {
            $this->warn('No task handlers registered. Add class names to config/conductor.php task_handlers.');

            return self::SUCCESS;
        }

        $this->info('Worker started (poll interval: ' . $pollInterval . 's). Press Ctrl+C to stop.');
        $worker->run();

        return self::SUCCESS;
    }
}
