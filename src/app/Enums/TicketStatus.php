<?php

namespace App\Enums;

enum TicketStatus: string
{
    case OPEN = 'Open';
    case RESOLVED = 'Resolved';

    public function label(): string
    {
        return match($this) {
            self::OPEN => 'Open',
            self::RESOLVED => 'Resolved',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::OPEN => 'blue',
            self::RESOLVED => 'green',
        };
    }
}
