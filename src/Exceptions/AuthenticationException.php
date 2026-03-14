<?php

declare(strict_types=1);

namespace Conductor\Exceptions;

/**
 * Thrown when Conductor API returns 401 Unauthorized (invalid or missing token).
 */
class AuthenticationException extends ConductorException
{
}
