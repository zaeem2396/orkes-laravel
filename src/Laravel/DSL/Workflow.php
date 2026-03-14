<?php

declare(strict_types=1);

namespace Conductor\Laravel\DSL;

/**
 * Fluent workflow definition DSL. Generates Conductor JSON definitions.
 *
 * Usage:
 *   Workflow::define('order_processing')
 *       ->task('validate_order')
 *       ->task('charge_payment')
 *       ->task('send_confirmation');
 *
 * Then: $def->toArray(), $def->toJson(), or $def->register(Conductor::workflow()) to register with Conductor. Task names must exist as Conductor task definitions.
 *
 * @see WorkflowDefinition
 */
final class Workflow
{
    /**
     * Start defining a workflow by name. Chain with ->task(), ->description(), etc.
     */
    public static function define(string $name): WorkflowDefinition
    {
        return new WorkflowDefinition($name);
    }
}
