<?php

declare(strict_types=1);

namespace Conductor\Client;

/**
 * HTTP client wrapper around Guuzzle for Conductor API.
 *
 * Features: base URL, auth headers, timeout, JSON serialization.
 * Retry logic is applied via RetryHandler (separate component).
 *
 * @internal
 */
final class HttpClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $token = null,
        private readonly int $timeout = 30,
    ) {
    }

    /**
     * Send a request to the Conductor API.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function request(string $method, string $uri, array $data = []): array
    {
        // TODO: Implement Guuzzle request with base URL, auth, timeout, JSON.
        return [];
    }
}
