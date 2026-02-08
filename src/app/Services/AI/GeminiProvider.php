<?php

namespace App\Services\AI;

use App\Contracts\AI\AIProviderInterface;
use App\DTOs\AIAnalysisDTO;
use App\Exceptions\AIProcessingException;
use App\Exceptions\AIProviderRateLimitException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

class GeminiProvider implements AIProviderInterface
{
    public function __construct(
        private string $apiKey,
        private string $model,
        private ClientInterface $client,
        private float $temperature = 0.3,
        private int $timeout = 30,
    ) {}

    public function analyzeTicket(string $title, string $description): AIAnalysisDTO
    {
        try {
            $response = $this->client->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent",
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'x-goog-api-key' => $this->apiKey,
                    ],
                    'json' => [
                        'contents' => [
                            [
                                'parts' => [
                                    [
                                        'text' => $this->buildPrompt($title, $description),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'timeout' => $this->timeout,
                ]
            );

            $body = json_decode($response->getBody()->getContents(), true);
            $content = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$content) {
                throw new AIProcessingException('Empty response from Gemini');
            }

            return $this->parseResponse($content);

        } catch (RequestException $e) {
            $statusCode = $e->getResponse()?->getStatusCode();

            if ($statusCode === 429 || $statusCode === 503) {
                throw new AIProviderRateLimitException('Gemini rate limit exceeded', $e);
            }

            throw new AIProcessingException('Gemini API request failed: ' . $e->getMessage(), 500, $e);
        }
    }

    private function buildPrompt(string $title, string $description): string
    {
        $systemPrompt = config('ai.prompts.system');
        $userContent = str_replace(
            ['{title}', '{description}'],
            [$title, $description],
            config('ai.prompts.user_template')
        );

        return $systemPrompt . "\n\n" . $userContent;
    }

    private function parseResponse(string $content): AIAnalysisDTO
    {
        $content = trim($content);
        $content = preg_replace('/^```json\s*/', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);

        $data = json_decode($content, true);

        if (!$data || !isset($data['category'], $data['sentiment'], $data['reply'])) {
            throw new AIProcessingException('Invalid response format from Gemini');
        }

        return new AIAnalysisDTO(
            category: $data['category'],
            sentiment: $data['sentiment'],
            suggestedReply: $data['reply'],
        );
    }
}
