<?php

declare(strict_types=1);

namespace Conductor\Laravel\Console;

use Conductor\Client\ConductorClient;
use Illuminate\Console\Command;

/**
 * Inspect Conductor: active workflows, failed workflows, pending tasks, workers.
 *
 * Example: php artisan conductor:inspect
 * Example: php artisan conductor:inspect --size=50
 */
final class InspectCommand extends Command
{
    protected $signature = 'conductor:inspect
                            {--size=20 : Max workflows to show per status}';

    protected $description = 'Show active and failed workflows (use --size to limit rows)';

    public function handle(ConductorClient $client): int
    {
        $size = (int) $this->option('size');
        if ($size < 1 || $size > 100) {
            $size = 20;
        }

        try {
            $running = $client->workflow()->search('status = RUNNING', 0, $size);
            $failed = $client->workflow()->search('status IN (FAILED, TIMED_OUT, TERMINATED)', 0, $size);

            $this->table(
                ['Workflow ID', 'Type', 'Status', 'Start Time'],
                $this->rowsFromResults($running['results']),
            );
            $this->info('Running: ' . $running['totalHits'] . ' total (showing up to ' . count($running['results']) . ')');

            $this->newLine();
            $this->table(
                ['Workflow ID', 'Type', 'Status', 'Failed Tasks'],
                $this->rowsFromFailedResults($failed['results']),
            );
            $this->info('Failed/Terminated: ' . $failed['totalHits'] . ' total (showing up to ' . count($failed['results']) . ')');

            return self::SUCCESS;
        } catch (\Conductor\Exceptions\ConductorException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     * @return array<int, array<string>>
     */
    private function rowsFromResults(array $results): array
    {
        $rows = [];
        foreach ($results as $w) {
            $rows[] = [
                $w['workflowId'] ?? '',
                $w['workflowType'] ?? '',
                $w['status'] ?? '',
                $w['startTime'] ?? '',
            ];
        }

        return $rows;
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     * @return array<int, array<string>>
     */
    private function rowsFromFailedResults(array $results): array
    {
        $rows = [];
        foreach ($results as $w) {
            $failedTasks = $w['failedTaskNames'] ?? [];
            $rows[] = [
                $w['workflowId'] ?? '',
                $w['workflowType'] ?? '',
                $w['status'] ?? '',
                is_array($failedTasks) ? implode(', ', $failedTasks) : (string) $failedTasks,
            ];
        }

        return $rows;
    }
}
