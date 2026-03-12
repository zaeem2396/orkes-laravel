<?php

declare(strict_types=1);

namespace Conductor\Client;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;

/**
 * HTTP client wrapper around Guuzzle for Conductor API.
 *
 * Features: base URL, auth headers, timeout, JSON serialization.
 * Retry logic is applied via RetryHandler (separate component).
 */
final class HttpClient
{
    private const DEFAULT_TIMEOUT = 30;

    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $token = null,
        private readonly int $timeout = self::DEFAULT_TIMEOUT,
        private readonly ?ClientInterface $guzzle = null,
    ) {
    }

    /**
     * Send a request to the Conductor API.
     *
     * @param  array  $data  Query params for GET/HEAD, JSON body for others (assoc or list).
     * @return array<string, mixed>
     *
     * @throws \Conductor\Exceptions\ConductorException
     */
    public function request(string $method, string $uri, array $data = []): array
    {
        $client = $this->guzzle ?? $this->createDefaultClient();
        $request = $this->buildRequest($method, $uri, $data);

        try {
            $response = $client->send($request);
        } catch (GuzzleException $e) {
            throw new \Conductor\Exceptions\ConductorException(
                'Conductor API request failed: ' . $e->getMessage(),
                (int) $e->getCode(),
                $e,
            );
        }

        $body = (string) $response->getBody();
        if ($body === '') {
            return [];
        }

        $decoded = json_decode($body, true);
        if (! is_array($decoded)) {
            throw new \Conductor\Exceptions\ConductorException(
                'Invalid JSON response from Conductor API',
                0,
            );
        }

        return $decoded;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function buildRequest(string $method, string $uri, array $data): Request
    {
        $fullUri = rtrim($this->baseUrl, '/') . '/' . ltrim($uri, '/');
        $headers = $this->defaultHeaders();

        $body = null;
        if ($data !== []) {
            if (in_array(strtoupper($method), ['GET', 'HEAD'], true)) {
                $fullUri .= (str_contains($fullUri, '?') ? '&' : '?') . http_build_query($data);
            } else {
                try {
                    $body = json_encode($data, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \Conductor\Exceptions\ConductorException(
                        'Failed to encode request body as JSON: ' . $e->getMessage(),
                        0,
                        $e,
                    );
                }
                $headers['Content-Type'] = 'application/json';
            }
        }

        return new Request($method, $fullUri, $headers, $body);
    }

    /**
     * @return array<string, string>
     */
    private function defaultHeaders(): array
    {
        $headers = [
            'Accept' => 'application/json',
        ];
        if ($this->token !== null && $this->token !== '') {
            $headers['Authorization'] = 'Bearer ' . $this->token;
        }

        return $headers;
    }

    private function createDefaultClient(): ClientInterface
    {
        $config = [
            'base_uri' => rtrim($this->baseUrl, '/') . '/',
            'timeout' => $this->timeout,
            'headers' => $this->defaultHeaders(),
        ];

        return new \GuzzleHttp\Client($config);
    }
}
