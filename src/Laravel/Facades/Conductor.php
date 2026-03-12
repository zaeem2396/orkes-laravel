<?php

declare(strict_types=1);

namespace Conductor\Laravel\Facades;

use Conductor\Client\ConductorClient;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin ConductorClient
 *
 * @see ConductorClient
 *
 * Usage:
 *   Conductor::workflow()->start('order_processing', ['order_id' => 1]);
 *   Conductor::tasks()->poll('process_payment');
 */
final class Conductor extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ConductorClient::class;
    }
}
