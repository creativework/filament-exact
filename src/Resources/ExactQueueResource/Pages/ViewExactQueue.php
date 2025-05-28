<?php

namespace CreativeWork\FilamentExact\Resources\ExactQueueResource\Pages;

use CreativeWork\FilamentExact\Actions\CancelJobAction;
use CreativeWork\FilamentExact\Actions\DuplicateTaskAction;
use CreativeWork\FilamentExact\Actions\PrioritizeJobAction;
use CreativeWork\FilamentExact\Resources\ExactQueueResource;
use Filament\Resources\Pages\ViewRecord;

class ViewExactQueue extends ViewRecord
{
    public static function getResource(): string
    {
        return config('filament-exact.resource', ExactQueueResource::class);
    }

    public function getTitle(): string
    {
        return __('Job').' #'.$this->record->id;
    }

    public function getHeaderActions(): array
    {
        return [
            PrioritizeJobAction::make('general'),
            DuplicateTaskAction::make('general'),
            CancelJobAction::make('general'),
        ];
    }
}
