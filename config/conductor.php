<?php

declare(strict_types=1);

return [

    'base_url' => env('CONDUCTOR_SERVER', 'http://localhost:8080/api'),

    'auth_token' => env('CONDUCTOR_TOKEN'),

    'timeout' => (int) env('CONDUCTOR_TIMEOUT', 30),

    'worker_concurrency' => (int) env('CONDUCTOR_WORKER_CONCURRENCY', 5),

    'poll_interval' => (int) env('CONDUCTOR_POLL_INTERVAL', 5),

    'retry_enabled' => (bool) env('CONDUCTOR_RETRY_ENABLED', false),

    'retry_max_attempts' => (int) env('CONDUCTOR_RETRY_MAX_ATTEMPTS', 3),

    'retry_initial_delay_ms' => (int) env('CONDUCTOR_RETRY_INITIAL_DELAY_MS', 1000),

];
