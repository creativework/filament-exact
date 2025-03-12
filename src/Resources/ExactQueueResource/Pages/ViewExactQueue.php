<?php

namespace CreativeWork\FilamentExact\Resources\ExactQueueResource\Pages;

use CreativeWork\FilamentExact\Resources\ExactQueueResource;
use Filament\Resources\Pages\ViewRecord;

class ViewExactQueue extends ViewRecord
{
    public static function getResource(): string
    {
        return config('filament-exact.resource', ExactQueueResource::class);
    }
}
