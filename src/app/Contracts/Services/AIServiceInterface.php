<?php

namespace App\Contracts\Services;

use App\DTOs\AIAnalysisDTO;

interface AIServiceInterface
{
    public function analyze(string $title, string $description): AIAnalysisDTO;
}
