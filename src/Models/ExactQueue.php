<?php

namespace creativework\FilamentExact\Models;

use creativework\FilamentExact\Enums\QueueStatusEnum;
use Database\Factories\ExactQueueFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ExactQueue extends Model
{
    use HasFactory;
    use MassPrunable;

    protected $fillable = [
        'job',
        'parameters',
        'status',
        'priority',
        'attempts',
        'response',
    ];

    protected $casts = [
        'status' => QueueStatusEnum::class,
        'parameters' => 'array',
    ];

    public function getTable(): string
    {
        return config('filament-exact.database.tables.queue', 'exact_queue');
    }

    public function prunable(): Builder
    {
        $pruneAfter = config('filament-exact.database.pruning.after', 30);

        return static::where('created_at', '<=', now()->subDays($pruneAfter));
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', QueueStatusEnum::PENDING)
            ->orWhere('status', QueueStatusEnum::PROCESSING);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', QueueStatusEnum::PENDING);
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', QueueStatusEnum::PROCESSING);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', QueueStatusEnum::COMPLETED);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', QueueStatusEnum::FAILED);
    }

    public function dispatch()
    {
        if (!class_exists($this->job)) {
            Log::error('Job class does not exist', ['job' => $this->job]);
            $this->update([
                'status' => QueueStatusEnum::FAILED,
                'response' => 'Job class does not exist',
            ]);

            return;
        }

        $instance = new $this->job(...$this->parameters);
        dispatch($instance);
        $this->update([
            'status' => QueueStatusEnum::PROCESSING,
        ]);
    }
}
