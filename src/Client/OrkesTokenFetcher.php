<?php

declare(strict_types=1);

namespace Conductor\Client;

use Conductor\Exceptions\ConductorException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Obtains a JWT from Orkes Conductor via POST {baseUrl}/token (application access key).
 *
 * @see https://orkes.io/content/sdks/authentication
 */
final class OrkesTokenFetcher
{
    /**
     * @throws ConductorException On network errors or invalid response body.
     */
    public static function fetchAccessToken(
        string $baseUrl,
        string $keyId,
        string $keySecret,
        int $timeout = 30,
        ?Client $httpClient = null,
    ): string {
        $uri = rtrim($baseUrl, '/') . '/token';
        $client = $httpClient ?? new Client([
            'timeout' => $timeout,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        try {
            $response = $client->post($uri, [
                'json' => [
                    'keyId' => $keyId,
                    'keySecret' => $keySecret,
                ],
            ]);
        } catch (GuzzleException $e) {
            throw new ConductorException('Failed to obtain Orkes token: ' . $e->getMessage(), 0, $e);
        }

        $body = (string) $response->getBody();
        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new ConductorException('Orkes token response was not valid JSON.');
        }

        if (! is_array($data) || ! isset($data['token']) || ! is_string($data['token']) || $data['token'] === '') {
            throw new ConductorException('Orkes token response missing non-empty "token" field.');
        }

        return $data['token'];
    }
}
