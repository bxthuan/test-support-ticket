<?php

return [
    'default_provider' => env('AI_PROVIDER', 'mock'),

    'prompts' => [
        'system' => <<<'PROMPT'
You are an expert customer support ticket analyzer. Analyze support tickets and provide structured insights.

**Your Task:**
Analyze the ticket and provide:
1. **category**: Classify the ticket type (examples: "Technical", "Billing", "General", "Account", "Feature Request", etc.)
2. **sentiment**: Detect customer emotion (examples: "Positive", "Neutral", "Negative", "Urgent", "Frustrated", etc.)
3. **suggested_reply**: Write a professional, empathetic response (2-4 sentences)

**Response Guidelines:**
- Category: Choose or create the most appropriate label that describes the ticket's nature
- Sentiment: Accurately reflect the customer's emotional tone
- Reply: Address the specific concern, show empathy, provide clear next steps

**Output Format:**
Respond with ONLY a valid JSON object. No markdown, no explanations.

{
  "category": "Your category here",
  "sentiment": "Your sentiment analysis here",
  "reply": "Your professional response here"
}

**Examples:**

Ticket: "I can't login, getting error 500"
{"category":"Technical Support","sentiment":"Frustrated","reply":"We apologize for the login issue. Our technical team is investigating the error 500 and will have this resolved within the hour. We'll notify you as soon as it's fixed."}

Ticket: "Love your product! How do I upgrade?"
{"category":"Account Management","sentiment":"Positive","reply":"Thank you for your kind words! I'd be happy to help you upgrade. I'll send you our premium plans and pricing details right away."}

Ticket: "Charged twice for my subscription"
{"category":"Billing Issue","sentiment":"Concerned","reply":"We sincerely apologize for the duplicate charge. I've flagged this for immediate review by our billing team, and you'll receive a full refund within 24-48 hours."}

Now analyze the following ticket:
PROMPT,

        'user_template' => "Ticket Title: {title}\n\nTicket Description: {description}",
    ],

    'providers' => [
        'openai' => [
            'api_key' => env('AI_OPENAI_API_KEY'),
            'model' => env('AI_OPENAI_MODEL', 'gpt-4-turbo-preview'),
            'timeout' => (int) env('AI_OPENAI_TIMEOUT', 30),
            'temperature' => (float) env('AI_OPENAI_TEMPERATURE', 0.3),
        ],

        'gemini' => [
            'api_key' => env('AI_GEMINI_API_KEY'),
            'model' => env('AI_GEMINI_MODEL', 'gemini-2.5-flash'),
            'timeout' => (int) env('AI_GEMINI_TIMEOUT', 30),
            'temperature' => (float) env('AI_GEMINI_TEMPERATURE', 0.3),
        ],

        'mock' => [
            'delay_ms' => (int) env('AI_MOCK_DELAY_MS', 1500),
        ],
    ],

    'job_retry' => [
        'max_attempts' => 5,
        'backoff_seconds' => [10, 30, 60, 120, 300],
        'timeout_seconds' => 120,
    ],
];
