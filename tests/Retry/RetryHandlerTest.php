<?php

declare(strict_types=1);

namespace Conductor\Tests\Retry;

use Conductor\Exceptions\RetryableException;
use Conductor\Retry\ExponentialDelayStrategy;
use Conductor\Retry\RetryHandler;
use PHPUnit\Framework\TestCase;

final class RetryHandlerTest extends TestCase
{
    public function test_execute_returns_result_on_success(): void
    {
        $handler = new RetryHandler(3);

        $result = $handler->execute(fn (): string => 'ok');

        $this->assertSame('ok', $result);
    }

    public function test_execute_retries_then_succeeds(): void
    {
        $attempts = 0;
        $handler = new RetryHandler(3, new ExponentialDelayStrategy(1, 2.0));

        $result = $handler->execute(function () use (&$attempts): string {
            $attempts++;
            if ($attempts < 2) {
                throw new RetryableException('Transient');
            }

            return 'ok';
        });

        $this->assertSame('ok', $result);
        $this->assertSame(2, $attempts);
    }

    public function test_execute_exhausts_retries_and_throws(): void
    {
        $handler = new RetryHandler(3, new ExponentialDelayStrategy(1, 2.0));

        $this->expectException(RetryableException::class);
        $this->expectExceptionMessage('Transient');

        $handler->execute(function (): void {
            throw new RetryableException('Transient');
        });
    }

    public function test_execute_does_not_retry_when_is_retryable_returns_false(): void
    {
        $attempts = 0;
        $handler = new RetryHandler(3);

        try {
            $handler->execute(
                function () use (&$attempts): void {
                    $attempts++;
                    throw new \RuntimeException('Client error');
                },
                fn (\Throwable $e): bool => $e instanceof RetryableException,
            );
        } catch (\RuntimeException $e) {
            $this->assertSame('Client error', $e->getMessage());
        }

        $this->assertSame(1, $attempts);
    }

    public function test_execute_with_max_attempts_one_does_not_retry(): void
    {
        $attempts = 0;
        $handler = new RetryHandler(1);

        try {
            $handler->execute(function () use (&$attempts): void {
                $attempts++;
                throw new RetryableException('Fail');
            });
        } catch (RetryableException $e) {
            $this->assertSame('Fail', $e->getMessage());
        }

        $this->assertSame(1, $attempts);
    }
}
