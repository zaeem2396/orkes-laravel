<?php

declare(strict_types=1);

/**
 * Example: Define order_processing workflow with DSL and output Conductor JSON.
 * Phase 10 documentation example.
 *
 * Run from project root (after composer install):
 *   php examples/order_processing_workflow.php
 *
 * Or in Laravel: use Conductor::workflow() and pass to register().
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Conductor\Laravel\DSL\Workflow;

$def = Workflow::define('order_processing')
    ->description('Process order: validate, charge, confirm')
    ->inputParameters(['order_id', 'customer_id'])
    ->task('validate_order')
    ->task('charge_payment')
    ->task('send_confirmation');

echo "Workflow definition JSON (Conductor schema v2):\n";
echo $def->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

// To register with Conductor when you have a WorkflowClient (e.g. Laravel: Conductor::workflow()):
// $def->register($workflowClient);
