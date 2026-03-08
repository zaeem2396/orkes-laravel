<?php

declare(strict_types=1);

namespace Conductor\Laravel\Workers;

/**
 * Base contract for Laravel task handlers (used with conductor:work).
 */
interface TaskHandler
{
    /**
     * Task type name (e.g. process_payment).
     */
    public function taskType(): string;

    /**
     * Handle the task. Return output data for COMPLETED or throw for FAILED.
     *
     * @param  array<string, mixed>  $task
     * @return array<string, mixed>
     */
    public function handle(array $task): array;
}
