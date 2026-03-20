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
     * Handle the task.
     *
     * Return output fields for a successful COMPLETED task (wrapped by conductor:work as status COMPLETED).
     *
     * Alternatively return an explicit result: array{
     *   status: 'COMPLETED'|'FAILED',
     *   outputData?: array<string, mixed>,
     *   reasonForIncompletion?: string,
     *   terminal?: bool
     * } (terminal + FAILED => FAILED_WITH_TERMINAL_ERROR on Conductor).
     *
     * @param  array<string, mixed>  $task
     * @return array<string, mixed>
     */
    public function handle(array $task): array;
}
