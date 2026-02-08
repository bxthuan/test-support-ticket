<?php

namespace App\Repositories;

use App\Contracts\Repositories\TicketRepositoryInterface;
use App\DTOs\AIAnalysisDTO;
use App\Models\Ticket;

class EloquentTicketRepository implements TicketRepositoryInterface
{
    public function create(array $data): Ticket
    {
        return Ticket::create($data);
    }

    public function findById(int $id): ?Ticket
    {
        return Ticket::find($id);
    }

    public function update(Ticket $ticket, array $data): bool
    {
        return $ticket->update($data);
    }

    public function updateAIAnalysis(Ticket $ticket, AIAnalysisDTO $analysis): bool
    {
        return $ticket->update([
            'category' => $analysis->category,
            'sentiment' => $analysis->sentiment,
            'suggested_reply' => $analysis->suggestedReply,
            'ai_processed_at' => now(),
        ]);
    }
}
