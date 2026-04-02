<?php

declare(strict_types=1);

namespace Conductor\Laravel\Providers;

use Conductor\Client\ConductorAuthResolver;
use Conductor\Client\ConductorClient;
use Conductor\Client\HttpClient;
use Conductor\Laravel\Console\FailuresCommand;
use Conductor\Laravel\Console\InspectCommand;
use Conductor\Laravel\Console\LocalCommand;
use Conductor\Laravel\Console\StartWorkflowCommand;
use Conductor\Laravel\Console\WorkerCommand;
use Conductor\Retry\ExponentialDelayStrategy;
use Conductor\Retry\RetryHandler;
use Illuminate\Support\ServiceProvider;

/**
 * Registers the Conductor SDK client from config and publishes config/conductor.php.
 * Config keys: base_url, auth_token, auth_key, auth_secret, auth_header_style, timeout,
 * worker_max_retries, poll_interval, retry_enabled, retry_max_attempts, retry_initial_delay_ms, task_handlers.
 * Commands: conductor:start, conductor:work, conductor:inspect, conductor:local, conductor:failures.
 */
final class ConductorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ConductorClient::class, function ($app) {
            $config = $app['config']->get('conductor', []);
            $baseUrl = (string) ($config['base_url'] ?? '');
            $timeout = (int) ($config['timeout'] ?? 30);

            $resolved = ConductorAuthResolver::resolve([
                'base_url' => $baseUrl,
                'token' => $config['auth_token'] ?? null,
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

            return new ConductorClient($http);
        });
    }

    public function boot(): void
    {
        $configPath = dirname(__DIR__, 3) . '/config/conductor.php';
        $this->mergeConfigFrom($configPath, 'conductor');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $configPath => $this->app->configPath('conductor.php'),
            ], 'conductor-config');

            // Register Artisan commands (conductor:start, conductor:work, conductor:inspect, conductor:local, conductor:failures)
            $this->commands([
                StartWorkflowCommand::class,
                WorkerCommand::class,
                InspectCommand::class,
                LocalCommand::class,
                FailuresCommand::class,
            ]);
        }
    }
}
