<?php

declare(strict_types=1);

namespace Conductor\Exceptions;

/**
 * Thrown when a workflow operation fails (start, get, terminate, etc.).
 */
class WorkflowException extends ConductorException
{
}
