<?php

declare(strict_types=1);

namespace Conductor\Laravel\Console;

use Conductor\Client\ConductorClient;
use Illuminate\Console\Command;

/**
 * Observability: list failed workflows, optional retry.
 *
 * Example: php artisan conductor:failures
 * Example: php artisan conductor:failures --size=100 --retry
 */
final class FailuresCommand extends Command
{
    protected $signature = 'conductor:failures
                            {--size=50 : Max workflows to list}
                            {--retry : Retry each listed failed workflow}';

    protected $description = 'List failed workflows (optional --retry to retry each)';

    public function handle(ConductorClient $client): int
    {
        $size = (int) $this->option('size');
        if ($size < 1 || $size > 200) {
            $size = 50;
        }
        $doRetry = (bool) $this->option('retry');

        try {
            $result = $client->workflow()->search(
                'status IN (FAILED, TIMED_OUT)',
                0,
                $size,
                'startTime:DESC',
            );

            $results = $result['results'];
            $total = $result['totalHits'];

            if ($total === 0) {
                $this->info('No failed workflows found.');

                return self::SUCCESS;
            }

            $rows = [];
            foreach ($results as $w) {
                $rows[] = [
                    $w['workflowId'] ?? '',
                    $w['workflowType'] ?? '',
                    $w['status'] ?? '',
                    isset($w['failedTaskNames']) && is_array($w['failedTaskNames'])
                        ? implode(', ', $w['failedTaskNames'])
                        : '',
                ];
            }
            $this->table(['Workflow ID', 'Type', 'Status', 'Failed Tasks'], $rows);
            $this->info("Total failed: {$total} (showing up to " . count($rows) . ')');

            if ($doRetry && count($rows) > 0) {
                $this->newLine();
                foreach ($results as $w) {
                    $workflowId = $w['workflowId'] ?? null;
                    if ($workflowId !== null && $workflowId !== '') {
                        try {
                            $client->workflow()->retryWorkflow((string) $workflowId);
                            $this->line("Retried: {$workflowId}");
                        } catch (\Throwable $e) {
                            $this->error("Retry failed for {$workflowId}: " . $e->getMessage());
                        }
                    }
                }
            }

            return self::SUCCESS;
        } catch (\Conductor\Exceptions\ConductorException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
