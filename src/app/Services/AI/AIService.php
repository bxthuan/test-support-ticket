<?php

namespace App\Services\AI;

use App\Contracts\AI\AIProviderInterface;
use App\Contracts\Services\AIServiceInterface;
use App\DTOs\AIAnalysisDTO;
use Illuminate\Support\Facades\Log;

class AIService implements AIServiceInterface
{
    public function __construct(
        private AIProviderInterface $provider
    ) {}

    public function analyze(string $title, string $description): AIAnalysisDTO
    {
        $startTime = microtime(true);

        Log::info('Starting AI analysis', [
            'title' => $title,
            'provider' => get_class($this->provider),
        ]);

        try {
            $result = $this->provider->analyzeTicket($title, $description);

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('AI analysis completed', [
                'duration_ms' => $duration,
                'category' => $result->category,
                'sentiment' => $result->sentiment,
            ]);

            return $result;

        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('AI analysis failed', [
                'duration_ms' => $duration,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            throw $e;
        }
    }
}
