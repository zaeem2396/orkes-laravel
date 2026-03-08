<?php

declare(strict_types=1);

namespace Conductor\Laravel\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            \Conductor\Laravel\Providers\ConductorServiceProvider::class,
        ];
    }
}
