<?php

namespace Tests\Feature;

use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_ticket_via_api(): void
    {
        $response = $this->postJson('/api/v1/tickets', [
            'title' => 'Cannot login to my account',
            'description' => 'I am getting an error message when trying to login. This is very frustrating.',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status' => [
                        'value',
                        'label',
                        'color',
                    ],
                    'processing_status',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'data' => [
                    'title' => 'Cannot login to my account',
                    'description' => 'I am getting an error message when trying to login. This is very frustrating.',
                    'status' => [
                        'value' => 'Open',
                        'label' => 'Open',
                        'color' => 'blue',
                    ],
                    'processing_status' => 'pending',
                ],
            ]);

        $this->assertDatabaseHas('tickets', [
            'title' => 'Cannot login to my account',
            'description' => 'I am getting an error message when trying to login. This is very frustrating.',
            'status' => 'Open',
        ]);
    }

    public function test_cannot_create_ticket_with_invalid_data(): void
    {
        $response = $this->postJson('/api/v1/tickets', [
            'title' => 'Hi',
            'description' => 'Short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description']);
    }

    public function test_can_view_ticket_by_id(): void
    {
        $ticket = Ticket::factory()->create([
            'title' => 'Test Ticket',
            'description' => 'Test description for the ticket',
            'status' => 'Open',
        ]);

        $response = $this->getJson("/api/v1/tickets/{$ticket->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'processing_status',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $ticket->id,
                    'title' => 'Test Ticket',
                    'description' => 'Test description for the ticket',
                ],
            ]);
    }

    public function test_returns_404_for_non_existent_ticket(): void
    {
        $response = $this->getJson('/api/v1/tickets/9999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Ticket with ID 9999 not found.',
            ]);
    }

    public function test_ticket_shows_ai_analysis_when_processed(): void
    {
        $ticket = Ticket::factory()->aiProcessed()->create([
            'title' => 'Billing Issue',
            'description' => 'I was charged twice',
            'category' => 'Billing',
            'sentiment' => 'Negative',
            'suggested_reply' => 'We apologize for the billing issue.',
        ]);

        $response = $this->getJson("/api/v1/tickets/{$ticket->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'ai_analysis' => [
                        'category',
                        'sentiment',
                        'suggested_reply',
                        'processed_at',
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    'processing_status' => 'completed',
                    'ai_analysis' => [
                        'category' => 'Billing',
                        'sentiment' => 'Negative',
                        'suggested_reply' => 'We apologize for the billing issue.',
                    ],
                ],
            ]);
    }

    public function test_complete_ticket_workflow(): void
    {
        $response = $this->postJson('/api/v1/tickets', [
            'title' => 'Feature Request',
            'description' => 'It would be great if you could add dark mode to the application',
        ]);

        $response->assertStatus(201);
        $ticketId = $response->json('data.id');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticketId,
            'title' => 'Feature Request',
            'status' => 'Open',
        ]);

        $response = $this->getJson("/api/v1/tickets/{$ticketId}");
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'processing_status',
                ],
            ]);

        $this->assertContains(
            $response->json('data.processing_status'),
            ['pending', 'completed']
        );
    }
}
