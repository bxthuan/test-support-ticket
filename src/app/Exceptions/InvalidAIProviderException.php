<?php

namespace App\Exceptions;

use Exception;

class InvalidAIProviderException extends Exception
{
    public function __construct(string $provider)
    {
        parent::__construct("Invalid AI provider: {$provider}. Supported providers: openai, mock, gemini.", 500);
    }
}
