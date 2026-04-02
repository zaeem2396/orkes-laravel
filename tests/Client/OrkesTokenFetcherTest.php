<?php

declare(strict_types=1);

namespace Conductor\Tests\Client;

use Conductor\Client\OrkesTokenFetcher;
use Conductor\Exceptions\ConductorException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class OrkesTokenFetcherTest extends TestCase
{
    public function test_fetch_returns_token_from_json(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{"token":"signed-jwt"}'),
        ]);
        $container = [];
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($container));
        $guzzle = new Client(['handler' => $stack]);

        $token = OrkesTokenFetcher::fetchAccessToken(
            'https://example.com/api',
            'key-id',
            'key-secret',
            30,
            $guzzle,
        );

        $this->assertSame('signed-jwt', $token);
        $this->assertCount(1, $container);
        $request = $container[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://example.com/api/token', (string) $request->getUri());
        $this->assertSame(
            '{"keyId":"key-id","keySecret":"key-secret"}',
            (string) $request->getBody(),
        );
    }

    public function test_fetch_throws_when_token_missing(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{}'),
        ]);
        $guzzle = new Client(['handler' => HandlerStack::create($mock)]);

        $this->expectException(ConductorException::class);
        $this->expectExceptionMessage('missing');

        OrkesTokenFetcher::fetchAccessToken('https://x.com/api', 'k', 's', 30, $guzzle);
    }
}
