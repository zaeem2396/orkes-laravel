<?php

declare(strict_types=1);

namespace Conductor\Exceptions;

/**
 * Marker for transient failures (5xx, timeouts). HttpClient uses this for retries.
 *
 * @internal
 */
class RetryableException extends ConductorException
{
}
