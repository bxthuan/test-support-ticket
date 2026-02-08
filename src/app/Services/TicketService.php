<?php

namespace App\Services;

use App\Contracts\Repositories\TicketRepositoryInterface;
use App\Contracts\Services\AIServiceInterface;
use App\Contracts\Services\TicketServiceInterface;
use App\DTOs\CreateTicketDTO;
use App\Enums\TicketStatus;
use App\Exceptions\AIProviderRateLimitException;
use App\Exceptions\AIProcessingException;
use App\Exceptions\TicketNotFoundException;
use App\Jobs\ProcessTicketWithAI;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketService implements TicketServiceInterface
{
    public function __construct(
        private TicketRepositoryInterface $ticketRepository,
        private AIServiceInterface $aiService,
    ) {}

    public function createTicket(CreateTicketDTO $dto): Ticket
    {
        return DB::transaction(function () use ($dto) {
            $ticket = $this->ticketRepository->create([
                'title' => $dto->title,
                'description' => $dto->description,
                'status' => TicketStatus::OPEN->value,
            ]);

            ProcessTicketWithAI::dispatch($ticket);

            Log::info('Ticket created and AI processing job dispatched', [
                'ticket_id' => $ticket->id,
            ]);

            return $ticket;
        });
    }

    public function getTicketById(int $id): Ticket
    {
        $ticket = $this->ticketRepository->findById($id);

        if (!$ticket) {
            throw new TicketNotFoundException($id);
        }

        return $ticket;
    }

    public function processTicketWithAI(Ticket $ticket): void
    {
        try {
            Log::info('Processing ticket with AI', ['ticket_id' => $ticket->id]);

            $analysis = $this->aiService->analyze($ticket->title, $ticket->description);

            $this->ticketRepository->updateAIAnalysis($ticket, $analysis);

            Log::info('Ticket AI processing completed', [
                'ticket_id' => $ticket->id,
                'category' => $analysis->category,
                'sentiment' => $analysis->sentiment,
            ]);

        } catch (AIProviderRateLimitException $e) {
            Log::warning('AI provider rate limit hit, will retry', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('Failed to process ticket with AI', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            throw new AIProcessingException('Failed to process ticket: ' . $e->getMessage(), 500, $e);
        }
    }
}
