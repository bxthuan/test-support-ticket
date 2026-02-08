<?php

namespace App\Contracts\Repositories;

use App\DTOs\AIAnalysisDTO;
use App\Models\Ticket;

interface TicketRepositoryInterface
{
    public function create(array $data): Ticket;

    public function findById(int $id): ?Ticket;

    public function update(Ticket $ticket, array $data): bool;

    public function updateAIAnalysis(Ticket $ticket, AIAnalysisDTO $analysis): bool;
}
