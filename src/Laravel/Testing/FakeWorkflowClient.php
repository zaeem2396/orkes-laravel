<?php

declare(strict_types=1);

namespace Conductor\Laravel\Testing;

/**
 * Fake workflow client that records started workflows for assertions.
 *
 * @internal
 */
final class FakeWorkflowClient
{
    /**
     * @param  callable(string, array): void  $onStart
     */
    public function __construct(
        private readonly mixed $onStart,
    ) {
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function start(string $name, array $input = [], ?string $correlationId = null, ?int $version = null): string
    {
        ($this->onStart)($name, $input);

        return 'fake-workflow-id';
    }
}
