<?php

declare(strict_types=1);

namespace Conductor\Laravel\Facades;

use Conductor\Client\ConductorClient;
use Conductor\Laravel\Testing\ConductorFake;
use Illuminate\Support\Facades\Facade;

/**
 * Laravel Facade for the Conductor SDK client (auto-discovered via composer extra.laravel).
 *
 * @mixin ConductorClient|ConductorFake
 *
 * @see ConductorClient
 *
 * Usage:
 *   Conductor::workflow()->start('order_processing', ['order_id' => 1]);
 *   Conductor::tasks()->poll('process_payment');
 *   Conductor::workers()->listen('my_task', $handler)->run();
 *
 * Testing: Conductor::fake(); then Conductor::workflow()->start(...); Conductor::assertWorkflowStarted('...');
 */
final class Conductor extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ConductorClient::class;
    }

    /**
     * Swap the Conductor client with a fake for testing. Returns the fake instance.
     */
    public static function fake(): ConductorFake
    {
        $fake = new ConductorFake();
        static::getFacadeApplication()->instance(ConductorClient::class, $fake);

        return $fake;
    }
}
