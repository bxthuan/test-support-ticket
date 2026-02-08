<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class AIProviderRateLimitException extends Exception
{
    public function __construct(string $message = "AI provider rate limit exceeded", ?Throwable $previous = null)
    {
        parent::__construct($message, 429, $previous);
    }
}
