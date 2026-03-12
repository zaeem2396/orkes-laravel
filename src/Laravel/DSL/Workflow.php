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
 */
final class Workflow
{
    /**
     * Start defining a workflow by name.
     */
    public static function define(string $name): WorkflowDefinition
    {
        return new WorkflowDefinition($name);
    }
}
