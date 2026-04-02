<?php

declare(strict_types=1);

namespace Conductor\Client;

use Conductor\Retry\ExponentialDelayStrategy;
use Conductor\Retry\RetryHandler;
use Conductor\Task\TaskClient;
use Conductor\Task\Worker;
use Conductor\Workflow\WorkflowClient;

/**
 * Main SDK entrypoint for Conductor / Orkes.
 *
 * Usage:
 *   $client = ConductorClient::fromArray([
 *       'base_url' => 'http://localhost:8080/api',
 *       'token'    => 'xyz',
 *   ]);
 *   $client->workflow()->start('order_processing', ['order_id' => 123]);
 */
final class ConductorClient
{
    private ?WorkflowClient $workflowClient = null;

    private ?TaskClient $taskClient = null;

    public function __construct(
        private readonly HttpClient $http,
    ) {
    }

    /**
     * Create client from config array.
     *
     * @param  array{
     *     base_url?: string,
     *     token?: string|null,
     *     auth_token?: string|null,
     *     auth_key?: string|null,
     *     auth_secret?: string|null,
     *     auth_header_style?: string|null,
     *     timeout?: int,
     *     retry_enabled?: bool,
     *     retry_max_attempts?: int,
     *     retry_initial_delay_ms?: int
     * }  $config
     */
    public static function fromArray(array $config): self
    {
        $baseUrl = (string) ($config['base_url'] ?? '');
        $timeout = isset($config['timeout']) ? (int) $config['timeout'] : 30;

        $resolved = ConductorAuthResolver::resolve([
            'base_url' => $baseUrl,
            'token' => $config['token'] ?? $config['auth_token'] ?? null,
            'auth_key' => $config['auth_key'] ?? null,
            'auth_secret' => $config['auth_secret'] ?? null,
            'auth_header_style' => $config['auth_header_style'] ?? null,
            'timeout' => $timeout,
        ]);

        $retryHandler = null;
        if (! empty($config['retry_enabled'])) {
            $retryHandler = new RetryHandler(
                maxAttempts: (int) ($config['retry_max_attempts'] ?? 3),
                delayStrategy: new ExponentialDelayStrategy(
                    initialDelayMs: (int) ($config['retry_initial_delay_ms'] ?? 1000),
                    multiplier: 2.0,
                ),
            );
        }

        $http = new HttpClient(
            baseUrl: $baseUrl,
            token: $resolved['token'],
            timeout: $timeout,
            guzzle: null,
            retryHandler: $retryHandler,
            authScheme: $resolved['authScheme'],
        );

        return new self($http);
    }

    public function workflow(): WorkflowClient
    {
        if ($this->workflowClient === null) {
            $this->workflowClient = new WorkflowClient($this->http);
        }

        return $this->workflowClient;
    }

    public function tasks(): TaskClient
    {
        if ($this->taskClient === null) {
            $this->taskClient = new TaskClient($this->http);
        }

        return $this->taskClient;
    }

    /**
     * Access worker factory / runner (uses TaskClient internally).
     */
    public function workers(): Worker
    {
        return new Worker($this->tasks());
    }
}
