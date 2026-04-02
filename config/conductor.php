<?php

declare(strict_types=1);

/**
 * Conductor Laravel configuration.
 * Publish with: php artisan vendor:publish --tag=conductor-config
 */
return [

    'base_url' => env('CONDUCTOR_SERVER_URL', env('CONDUCTOR_SERVER', 'http://localhost:8080/api')),

    'auth_token' => env('CONDUCTOR_TOKEN'),

    'auth_key' => env('CONDUCTOR_AUTH_KEY'),

    'auth_secret' => env('CONDUCTOR_AUTH_SECRET'),

    'auth_header_style' => env('CONDUCTOR_AUTH_HEADER_STYLE', 'bearer'),

    'timeout' => (int) env('CONDUCTOR_TIMEOUT', 30),

    /*
     * Retries when the task handler throws (per task pick-up), not Conductor task retries.
     * Scale throughput by running multiple `php artisan conductor:work` processes.
     */
    'worker_max_retries' => (int) env('CONDUCTOR_WORKER_MAX_RETRIES', 0),

    'poll_interval' => (int) env('CONDUCTOR_POLL_INTERVAL', 5),

    'retry_enabled' => (bool) env('CONDUCTOR_RETRY_ENABLED', false),

    'retry_max_attempts' => (int) env('CONDUCTOR_RETRY_MAX_ATTEMPTS', 3),

    'retry_initial_delay_ms' => (int) env('CONDUCTOR_RETRY_INITIAL_DELAY_MS', 1000),

    /*
     * Task handler class names for conductor:work and conductor:local.
     * Each must implement Conductor\Laravel\Workers\TaskHandler (taskType + handle).
     */
    'task_handlers' => [],

];
