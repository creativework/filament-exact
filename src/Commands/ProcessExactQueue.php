<?php

namespace creativework\FilamentExact\Commands;

use creativework\FilamentExact\Enums\QueueStatusEnum;
use creativework\FilamentExact\Models\ExactQueue;
use Illuminate\Console\Command;

class ProcessExactQueue extends Command
{
    protected $signature = 'exact:process-queue';

    protected $description = 'Process the ExactQueue table and dispatches job dynamically';

    public function handle(): void
    {
        $jobs = ExactQueue::where('status', QueueStatusEnum::PENDING)
            ->orderBy('priority', 'asc')
            ->orderBy('created_at', 'asc')
            ->limit(1)
            ->get();

        foreach ($jobs as $job) {
            $job->dispatch();
        }
    }
}
