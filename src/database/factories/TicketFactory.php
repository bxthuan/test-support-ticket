<?php

namespace Database\Factories;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(3),
            'status' => TicketStatus::OPEN->value,
            'category' => null,
            'sentiment' => null,
            'suggested_reply' => null,
            'ai_processed_at' => null,
            'ai_retry_count' => 0,
        ];
    }

    public function aiProcessed(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => fake()->randomElement([
                'Technical Support',
                'Billing',
                'General',
                'Feature Request',
                'Account Management',
            ]),
            'sentiment' => fake()->randomElement([
                'Positive',
                'Neutral',
                'Negative',
                'Urgent',
            ]),
            'suggested_reply' => fake()->paragraph(2),
            'ai_processed_at' => now(),
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::RESOLVED->value,
        ]);
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::OPEN->value,
        ]);
    }
}
