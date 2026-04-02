<?php

declare(strict_types=1);

namespace Conductor\Tests\Client;

use Conductor\Client\ConductorAuthResolver;
use Conductor\Client\HttpClient;
use PHPUnit\Framework\TestCase;

final class ConductorAuthResolverTest extends TestCase
{
    public function test_resolve_returns_null_token_and_bearer_when_empty(): void
    {
        $r = ConductorAuthResolver::resolve([]);

        $this->assertNull($r['token']);
        $this->assertSame(HttpClient::AUTH_SCHEME_BEARER, $r['authScheme']);
    }

    public function test_resolve_uses_bearer_for_static_token_by_default(): void
    {
        $r = ConductorAuthResolver::resolve(['token' => 't']);

        $this->assertSame('t', $r['token']);
        $this->assertSame(HttpClient::AUTH_SCHEME_BEARER, $r['authScheme']);
    }

    public function test_resolve_uses_x_authorization_when_auth_header_style_orkes(): void
    {
        $r = ConductorAuthResolver::resolve([
            'token' => 't',
            'auth_header_style' => 'orkes',
        ]);

        $this->assertSame(HttpClient::AUTH_SCHEME_X_AUTHORIZATION, $r['authScheme']);
    }

    public function test_resolve_static_token_wins_over_auth_key(): void
    {
        $r = ConductorAuthResolver::resolve([
            'base_url' => 'http://localhost:8080/api',
            'token' => 'preset',
            'auth_key' => 'kid',
            'auth_secret' => 'secret',
        ]);

        $this->assertSame('preset', $r['token']);
        $this->assertSame(HttpClient::AUTH_SCHEME_BEARER, $r['authScheme']);
    }
}
