<?php

namespace App\Contracts\Services;

use App\DTOs\CreateTicketDTO;
use App\Models\Ticket;

interface TicketServiceInterface
{
    public function createTicket(CreateTicketDTO $dto): Ticket;

    public function getTicketById(int $id): Ticket;

    public function processTicketWithAI(Ticket $ticket): void;
}
