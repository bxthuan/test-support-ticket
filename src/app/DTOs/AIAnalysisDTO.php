<?php

namespace App\DTOs;

readonly class AIAnalysisDTO
{
    public function __construct(
        public string $category,
        public string $sentiment,
        public string $suggestedReply,
    ) {}
}
