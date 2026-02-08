<?php

namespace App\DTOs;

use App\Http\Requests\StoreTicketRequest;

readonly class CreateTicketDTO
{
    public function __construct(
        public string $title,
        public string $description,
    ) {}

    public static function fromRequest(StoreTicketRequest $request): self
    {
        return new self(
            title: $request->validated('title'),
            description: $request->validated('description'),
        );
    }
}
