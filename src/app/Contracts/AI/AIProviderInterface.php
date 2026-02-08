<?php

namespace App\Contracts\AI;

use App\DTOs\AIAnalysisDTO;

interface AIProviderInterface
{
    public function analyzeTicket(string $title, string $description): AIAnalysisDTO;
}
