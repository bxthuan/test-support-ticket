<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class AIProcessingException extends Exception
{
    public function __construct(string $message = "Failed to process ticket with AI", int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
