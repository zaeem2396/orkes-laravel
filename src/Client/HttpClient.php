<?php

declare(strict_types=1);

namespace Conductor\Client;

use Conductor\Exceptions\AuthenticationException;
use Conductor\Exceptions\ConductorException;
use Conductor\Exceptions\RetryableException;
use Conductor\Retry\RetryHandler;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

/**
 * HTTP client wrapper around Guuzzle for Conductor API.
 *
 * Features: base URL, auth headers, timeout, JSON serialization.
 * Optional RetryHandler retries on 5xx and connection timeouts.
 */
final class HttpClient
{
    private const DEFAULT_TIMEOUT = 30;

    /** Authorization: Bearer {token} (OSS / many Conductor deployments). */
    public const AUTH_SCHEME_BEARER = 'bearer';

    /** X-Authorization: {token} (Orkes Conductor API). */
    public const AUTH_SCHEME_X_AUTHORIZATION = 'x_authorization';

    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $token = null,
        private readonly int $timeout = self::DEFAULT_TIMEOUT,
        private readonly ?ClientInterface $guzzle = null,
        private readonly ?RetryHandler $retryHandler = null,
        private readonly string $authScheme = self::AUTH_SCHEME_BEARER,
    ) {
    }

    /**
     * Send a request to the Conductor API.
     *
     * @param  array  $data  Query params for GET/HEAD, JSON body for others (assoc or list).
     * @return array<string, mixed>
     *
     * @throws AuthenticationException On 401 Unauthorized.
     * @throws ConductorException On other API or network errors.
     */
    public function request(string $method, string $uri, array $data = []): array
    {
        $doRequest = fn (): array => $this->executeRequest($method, $uri, $data);

        if ($this->retryHandler !== null) {
            return $this->retryHandler->execute(
                $doRequest,
                fn (\Throwable $e): bool => $e instanceof RetryableException,
            );
        }

        return $doRequest();
    }

    /**
     * Perform a single request (no retry). Used internally.
     *
     * @return array<string, mixed>
     *
     * @throws AuthenticationException
     * @throws ConductorException
     * @throws RetryableException
     */
    private function executeRequest(string $method, string $uri, array $data): array
    {
        $client = $this->guzzle ?? $this->createDefaultClient();
        $request = $this->buildRequest($method, $uri, $data);

        try {
            $response = $client->send($request);
        } catch (GuzzleException $e) {
            $this->throwMappedException($e);

            return [];
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        if ($body === '') {
            return [];
        }

        $decoded = json_decode($body, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Conductor OSS 3.x and Orkes often return a raw workflow execution id (plain text) for POST /workflow/...
        if ($status >= 200 && $status < 300) {
            $trimmed = trim($body, "\" \n\r\t");
            if ($trimmed !== '' && strpbrk($trimmed, "\n\r") === false) {
                if (preg_match(
                    '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
                    $trimmed,
                ) === 1) {
                    return ['workflowId' => $trimmed];
                }
                // Orkes may use non-hex characters in the execution id segments.
                if (strlen($trimmed) <= 128 && preg_match('/^[a-zA-Z0-9._:-]+$/', $trimmed) === 1) {
                    return ['workflowId' => $trimmed];
                }
            }
        }

        throw new ConductorException(
            'Invalid JSON response from Conductor API',
            0,
        );
    }

    /**
     * @throws AuthenticationException
     * @throws ConductorException
     * @throws RetryableException
     */
    private function throwMappedException(GuzzleException $e): void
    {
        if ($e instanceof ConnectException) {
            throw new RetryableException(
                'Conductor API connection failed: ' . $e->getMessage(),
                (int) $e->getCode(),
                $e,
            );
        }

        if ($e instanceof RequestException) {
            $response = $e->getResponse();
            if ($response !== null) {
                $status = $response->getStatusCode();
                if ($status === 401) {
                    throw new AuthenticationException(
                        'Conductor API authentication failed (401 Unauthorized)',
                        401,
                        $e,
                    );
                }
                if ($status >= 500) {
                    throw new RetryableException(
                        'Conductor API server error (' . $status . '): ' . $e->getMessage(),
                        $status,
                        $e,
                    );
                }
            }
        }

        throw new ConductorException(
            'Conductor API request failed: ' . $e->getMessage(),
            (int) $e->getCode(),
            $e,
        );
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
                    throw new ConductorException(
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
            if ($this->authScheme === self::AUTH_SCHEME_X_AUTHORIZATION) {
                $headers['X-Authorization'] = $this->token;
            } else {
                $headers['Authorization'] = 'Bearer ' . $this->token;
            }
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
