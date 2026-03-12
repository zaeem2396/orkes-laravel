<?php

declare(strict_types=1);

return [

    'base_url' => env('CONDUCTOR_SERVER', 'http://localhost:8080/api'),

    'auth_token' => env('CONDUCTOR_TOKEN'),

    'timeout' => (int) env('CONDUCTOR_TIMEOUT', 30),

    'worker_concurrency' => (int) env('CONDUCTOR_WORKER_CONCURRENCY', 5),

    'poll_interval' => (int) env('CONDUCTOR_POLL_INTERVAL', 5),

];
