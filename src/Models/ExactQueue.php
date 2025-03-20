<?php

namespace CreativeWork\FilamentExact\Models;

use CreativeWork\FilamentExact\Enums\QueueStatusEnum;
use CreativeWork\FilamentExact\Mail\ExactErrorMail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

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

    public function notify($id, $message)
    {
        $recipients = config('filament-exact.notifications.mail.to');
        if (! $recipients) {
            return;
        }

        foreach ($recipients as $recipient) {
            Mail::to($recipient)->send(new ExactErrorMail("Error in Exact Queue task $id", $message));
        }
    }
}
