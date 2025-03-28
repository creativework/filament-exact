<?php

namespace CreativeWork\FilamentExact\Jobs;

use CreativeWork\FilamentExact\Services\ExactService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;

abstract class ExactQueueJob implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Define job middleware.
     */
    public function middleware(): array
    {
        return [
            new RateLimited('exact'),
        ];
    }

    /**
     * Your custom job must implement this method
     */
    abstract public function handle(ExactService $service): void;
}
