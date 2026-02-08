<?php

namespace App\Jobs;

use App\Contracts\Services\TicketServiceInterface;
use App\Exceptions\AIProviderRateLimitException;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessTicketWithAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $backoff = [10, 30, 60, 120, 300];
    public $timeout = 120;
    public $maxExceptions = 3;

    public function __construct(
        public Ticket $ticket
    ) {}

    public function handle(TicketServiceInterface $service): void
    {
        $attempt = $this->attempts();

        Log::info('Processing ticket with AI - Attempt ' . $attempt, [
            'ticket_id' => $this->ticket->id,
            'attempt' => $attempt,
            'max_tries' => $this->tries,
        ]);

        $this->ticket->increment('ai_retry_count');

        try {
            $service->processTicketWithAI($this->ticket);

            Log::info('Ticket AI processing succeeded', [
                'ticket_id' => $this->ticket->id,
                'attempt' => $attempt,
            ]);

        } catch (AIProviderRateLimitException $e) {
            Log::warning('Rate limit hit, releasing job back to queue', [
                'ticket_id' => $this->ticket->id,
                'attempt' => $attempt,
                'next_retry_seconds' => $this->backoff[$attempt - 1] ?? 300,
            ]);

            $this->release($this->backoff[$attempt - 1] ?? 300);

        } catch (\Exception $e) {
            Log::error('AI processing attempt failed', [
                'ticket_id' => $this->ticket->id,
                'attempt' => $attempt,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Ticket AI processing permanently failed after all retries', [
            'ticket_id' => $this->ticket->id,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
            'exception' => get_class($exception),
        ]);
    }

    public function retryUntil()
    {
        return now()->addHour();
    }
}
