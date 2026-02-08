<?php

namespace App\Services\AI;

use App\Contracts\AI\AIProviderInterface;
use App\DTOs\AIAnalysisDTO;

class MockAIProvider implements AIProviderInterface
{
    public function __construct(
        private int $delayMs = 1500
    ) {}

    public function analyzeTicket(string $title, string $description): AIAnalysisDTO
    {
        usleep($this->delayMs * 1000);

        $text = strtolower($title . ' ' . $description);

        $category = $this->detectCategory($text);
        $sentiment = $this->detectSentiment($text);
        $suggestedReply = $this->generateSuggestedReply($category, $sentiment);

        return new AIAnalysisDTO(
            category: $category,
            sentiment: $sentiment,
            suggestedReply: $suggestedReply,
        );
    }

    private function detectCategory(string $text): string
    {
        if (str_contains($text, 'login') || str_contains($text, 'error') || str_contains($text, 'bug') || str_contains($text, 'technical')) {
            return 'Technical Support';
        }

        if (str_contains($text, 'payment') || str_contains($text, 'invoice') || str_contains($text, 'billing') || str_contains($text, 'charge')) {
            return 'Billing';
        }

        if (str_contains($text, 'feature') || str_contains($text, 'request')) {
            return 'Feature Request';
        }

        if (str_contains($text, 'account')) {
            return 'Account Management';
        }

        return 'General';
    }

    private function detectSentiment(string $text): string
    {
        if (str_contains($text, 'angry') || str_contains($text, 'frustrated') || str_contains($text, 'hate') || str_contains($text, 'terrible')) {
            return 'Negative';
        }

        if (str_contains($text, 'love') || str_contains($text, 'great') || str_contains($text, 'thank') || str_contains($text, 'excellent')) {
            return 'Positive';
        }

        if (str_contains($text, 'urgent') || str_contains($text, 'immediately') || str_contains($text, 'asap')) {
            return 'Urgent';
        }

        return 'Neutral';
    }

    private function generateSuggestedReply(string $category, string $sentiment): string
    {
        $greeting = $this->getGreeting($sentiment);
        $body = $this->getBody($category);

        return $greeting . $body;
    }

    private function getGreeting(string $sentiment): string
    {
        if ($sentiment === 'Negative' || $sentiment === 'Urgent') {
            return 'We sincerely apologize for the inconvenience you are experiencing. ';
        }

        if ($sentiment === 'Positive') {
            return 'Thank you for reaching out! We are glad to help. ';
        }

        return 'Thank you for contacting us. ';
    }

    private function getBody(string $category): string
    {
        if ($category === 'Technical Support') {
            return 'Our technical team has been notified and will investigate this issue promptly. We will keep you updated on the progress.';
        }

        if ($category === 'Billing') {
            return 'Our billing department will review your account and get back to you within 24 hours with a resolution.';
        }

        if ($category === 'Feature Request') {
            return 'We appreciate your feedback! Our product team will review your feature request and consider it for future updates.';
        }

        if ($category === 'Account Management') {
            return 'I will help you with your account right away. You can expect a detailed response within the next hour.';
        }

        return 'We have received your inquiry and will respond with more information shortly.';
    }
}
