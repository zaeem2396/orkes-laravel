<?php

declare(strict_types=1);

namespace Conductor\Laravel\Facades;

use Conductor\Client\ConductorClient;
use Illuminate\Support\Facades\Facade;

/**
 * Laravel Facade for the Conductor SDK client (auto-discovered via composer extra.laravel).
 *
 * @mixin ConductorClient
 *
 * @see ConductorClient
 *
 * Usage:
 *   Conductor::workflow()->start('order_processing', ['order_id' => 1]);
 *   Conductor::tasks()->poll('process_payment');
 *   Conductor::workers()->listen('my_task', $handler)->run();
 */
final class Conductor extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ConductorClient::class;
    }
}
