<?php

namespace App\Factories;

use App\Contracts\AI\AIProviderInterface;
use App\Exceptions\InvalidAIProviderException;
use App\Services\AI\GeminiProvider;
use App\Services\AI\MockAIProvider;
use App\Services\AI\OpenAIProvider;
use GuzzleHttp\Client;

class AIProviderFactory
{
    public function make(?string $provider = null): AIProviderInterface
    {
        $provider = $provider ?? config('ai.default_provider');

        return match ($provider) {
            'openai' => $this->makeOpenAIProvider(),
            'gemini' => $this->makeGeminiProvider(),
            'mock' => $this->makeMockProvider(),
            default => throw new InvalidAIProviderException($provider),
        };
    }

    private function makeOpenAIProvider(): OpenAIProvider
    {
        $config = config('ai.providers.openai');

        return new OpenAIProvider(
            apiKey: $config['api_key'],
            model: $config['model'],
            client: new Client(),
            temperature: $config['temperature'],
            timeout: $config['timeout'],
        );
    }

    private function makeGeminiProvider(): GeminiProvider
    {
        $config = config('ai.providers.gemini');

        return new GeminiProvider(
            apiKey: $config['api_key'],
            model: $config['model'],
            client: new Client(),
            temperature: $config['temperature'],
            timeout: $config['timeout'],
        );
    }

    private function makeMockProvider(): MockAIProvider
    {
        $config = config('ai.providers.mock');

        return new MockAIProvider(
            delayMs: $config['delay_ms'],
        );
    }
}
