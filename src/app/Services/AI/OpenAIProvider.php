<?php

namespace App\Services\AI;

use App\Contracts\AI\AIProviderInterface;
use App\DTOs\AIAnalysisDTO;
use App\Exceptions\AIProcessingException;
use App\Exceptions\AIProviderRateLimitException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

class OpenAIProvider implements AIProviderInterface
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
            $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => config('ai.prompts.system'),
                        ],
                        [
                            'role' => 'user',
                            'content' => $this->buildPrompt($title, $description),
                        ],
                    ],
                    'temperature' => $this->temperature,
                ],
                'timeout' => $this->timeout,
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            $content = $body['choices'][0]['message']['content'] ?? null;

            if (!$content) {
                throw new AIProcessingException('Empty response from OpenAI');
            }

            return $this->parseResponse($content);

        } catch (RequestException $e) {
            if ($e->getResponse()?->getStatusCode() === 429) {
                throw new AIProviderRateLimitException('OpenAI rate limit exceeded', $e);
            }
            throw new AIProcessingException('OpenAI API request failed: ' . $e->getMessage(), 500, $e);
        }
    }

    private function buildPrompt(string $title, string $description): string
    {
        $template = config('ai.prompts.user_template');
        return str_replace(['{title}', '{description}'], [$title, $description], $template);
    }

    private function parseResponse(string $content): AIAnalysisDTO
    {
        $content = trim($content);
        $content = preg_replace('/^```json\s*/', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);

        $data = json_decode($content, true);

        if (!$data || !isset($data['category'], $data['sentiment'], $data['reply'])) {
            throw new AIProcessingException('Invalid response format from OpenAI');
        }

        return new AIAnalysisDTO(
            category: $data['category'],
            sentiment: $data['sentiment'],
            suggestedReply: $data['reply'],
        );
    }
}
