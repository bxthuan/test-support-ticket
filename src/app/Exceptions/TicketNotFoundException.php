<?php

namespace App\Exceptions;

use Exception;

class TicketNotFoundException extends Exception
{
    public function __construct(int $ticketId)
    {
        parent::__construct("Ticket with ID {$ticketId} not found.", 404);
    }
}
