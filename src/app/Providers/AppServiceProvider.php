<?php

namespace App\Providers;

use App\Contracts\AI\AIProviderInterface;
use App\Contracts\Repositories\TicketRepositoryInterface;
use App\Contracts\Services\AIServiceInterface;
use App\Contracts\Services\TicketServiceInterface;
use App\Factories\AIProviderFactory;
use App\Repositories\EloquentTicketRepository;
use App\Services\AI\AIService;
use App\Services\TicketService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TicketRepositoryInterface::class, EloquentTicketRepository::class);
        $this->app->bind(TicketServiceInterface::class, TicketService::class);
        $this->app->bind(AIServiceInterface::class, AIService::class);

        $this->app->singleton(AIProviderInterface::class, function ($app) {
            return $app->make(AIProviderFactory::class)->make();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
