<?php

declare(strict_types=1);

namespace Conductor\Laravel\Providers;

use Conductor\Client\ConductorClient;
use Conductor\Client\HttpClient;
use Conductor\Laravel\Console\InspectCommand;
use Conductor\Laravel\Console\StartWorkflowCommand;
use Conductor\Laravel\Console\WorkerCommand;
use Illuminate\Support\ServiceProvider;

/**
 * Registers the Conductor SDK client and publishes config.
 */
final class ConductorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ConductorClient::class, function ($app) {
            $config = $app['config']->get('conductor', []);
            $http = new HttpClient(
                baseUrl: (string) ($config['base_url'] ?? ''),
                token: isset($config['auth_token']) ? (string) $config['auth_token'] : null,
                timeout: (int) ($config['timeout'] ?? 30),
            );

            return new ConductorClient($http);
        });
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/conductor.php', 'conductor');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/conductor.php' => $this->app->configPath('conductor.php'),
            ], 'conductor-config');

            $this->commands([
                StartWorkflowCommand::class,
                WorkerCommand::class,
                InspectCommand::class,
            ]);
        }
    }
}
