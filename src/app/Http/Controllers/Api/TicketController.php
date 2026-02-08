<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\TicketServiceInterface;
use App\DTOs\CreateTicketDTO;
use App\Exceptions\TicketNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Resources\TicketResource;
use Illuminate\Http\JsonResponse;

class TicketController extends Controller
{
    public function __construct(
        private TicketServiceInterface $ticketService
    ) {}

    public function store(StoreTicketRequest $request): JsonResponse
    {
        $dto = CreateTicketDTO::fromRequest($request);
        $ticket = $this->ticketService->createTicket($dto);

        return (new TicketResource($ticket))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $ticket = $this->ticketService->getTicketById($id);
            return (new TicketResource($ticket))->response();
        } catch (TicketNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
