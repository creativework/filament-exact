<?php

namespace creativework\FilamentExact\Jobs;

use creativework\FilamentExact\Services\ExactService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Picqer\Financials\Exact\Connection;

abstract class ExactQueueJob implements ShouldQueue
{

    use Queueable, SerializesModels;

    /**
     * Define job middleware.
     *
     * @return array
     */
    public function middleware(): array
    {
        return [
            new RateLimited('exact'),
        ];
    }

    /**
     * Your custom job must implement this method
     *
     * @param  Connection  $connection
     * @return void
     */
    abstract public function handle(Connection $connection): void;




}
