<?php

declare(strict_types=1);

namespace Conductor\Client;

/**
 * Resolves Conductor API token and HTTP auth header style (Bearer vs Orkes X-Authorization).
 */
final class ConductorAuthResolver
{
    /**
     * @param  array{
     *     base_url?: string,
     *     token?: string|null,
     *     auth_key?: string|null,
     *     auth_secret?: string|null,
     *     auth_header_style?: string|null,
     *     timeout?: int
     * }  $config
     * @return array{token: string|null, authScheme: string}
     */
    public static function resolve(array $config): array
    {
        $baseUrl = (string) ($config['base_url'] ?? '');
        $timeout = (int) ($config['timeout'] ?? 30);

        $rawToken = $config['token'] ?? null;
        $token = is_string($rawToken) && $rawToken !== '' ? $rawToken : null;

        $fromOrkesKey = false;
        if ($token === null && $baseUrl !== '' && self::nonEmptyString($config['auth_key'] ?? null) && self::nonEmptyString($config['auth_secret'] ?? null)) {
            $token = OrkesTokenFetcher::fetchAccessToken(
                $baseUrl,
                (string) $config['auth_key'],
                (string) $config['auth_secret'],
                $timeout,
            );
            $fromOrkesKey = true;
        }

        $style = strtolower((string) ($config['auth_header_style'] ?? 'bearer'));
        $authScheme = HttpClient::AUTH_SCHEME_BEARER;
        if ($fromOrkesKey) {
            $authScheme = HttpClient::AUTH_SCHEME_X_AUTHORIZATION;
        } elseif (in_array($style, ['orkes', 'x_authorization'], true)) {
            $authScheme = HttpClient::AUTH_SCHEME_X_AUTHORIZATION;
        }

        return [
            'token' => $token,
            'authScheme' => $authScheme,
        ];
    }

    private static function nonEmptyString(mixed $value): bool
    {
        return is_string($value) && $value !== '';
    }
}
