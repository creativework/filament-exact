<?php

namespace creativework\FilamentExact\Commands;

use creativework\FilamentExact\Enums\QueueStatusEnum;
use creativework\FilamentExact\Mail\ExactErrorMail;
use creativework\FilamentExact\Models\ExactQueue;
use creativework\FilamentExact\Services\ExactService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Log;

class ProcessExactQueue extends Command
{
    protected $signature = 'exact:process-queue';

    protected $description = 'Process the ExactQueue table and dispatches job dynamically';

    public function handle(ExactService $exactService): void
    {
        $queue = ExactQueue::where('status', QueueStatusEnum::PENDING)
            ->orderBy('priority', 'asc')
            ->orderBy('created_at', 'asc')
            ->first();

        if (! $queue) {
            return;
        }

        try {
            $queue->update(['status' => QueueStatusEnum::PENDING]);

            $jobClass = $queue->job;
            $parameters = $queue->parameters ?? [];

            // Instantiate the job; assuming parameters are passed as an associative array
            $job = new $jobClass(...array_values($parameters));

            // Get the authorized connection (automatic authorization happens here)
            $connection = $exactService->getConnection();

            // Execute the job's handle method with the connection
            $job->handle($connection);

            $queue->update(['status' => QueueStatusEnum::COMPLETED]);
        } catch (\Exception $e) {
            Log::error('Error processing ExactQueue job', ['job' => $queue->id, 'error' => $e->getMessage()]);
            $queue->update(['status' => QueueStatusEnum::FAILED, 'response' => $e->getMessage()]);

            $recipients = config('filament-exact.notifications.mail.to');
            if ($recipients) {
                foreach ($recipients as $recipient) {
                    Mail::to($recipient)->send(new ExactErrorMail("Error in ExactQueue job $queue->id", $e->getMessage()));
                }
            }
        }
    }
}
