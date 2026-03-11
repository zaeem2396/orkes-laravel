<?php

declare(strict_types=1);

namespace Conductor\Exceptions;

/**
 * Thrown when a task operation fails (poll, complete, fail, update, ack).
 */
class TaskException extends ConductorException
{
}
