<?php

namespace CreativeWork\FilamentExact\Actions;

use CreativeWork\FilamentExact\Enums\QueueStatusEnum;
use CreativeWork\FilamentExact\Models\ExactQueue;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

class DuplicateTaskAction
{
    public static function make($type = 'general'): Action | TableAction | BulkAction
    {
        $modelClass = config('filament-exact.model');

        switch ($type) {
            case 'table':
                return TableAction::make('duplicate')
                    ->label(__('Duplicate Job'))
                    ->color('secondary')
                    ->icon('heroicon-o-document-duplicate')
                    ->requiresConfirmation()
                    ->modalDescription(__('Are you sure you want to duplicate this job?'))
                    ->visible(fn ($record) => auth()->user()?->can('duplicate', $record) ?? true)
                    ->action(function (TableAction $action, ExactQueue $record) {
                        return static::handle($action, $record);
                    });

                break;
            case 'bulk':
                return BulkAction::make('duplicate')
                    ->label(__('Duplicate Job'))
                    ->color('secondary')
                    ->icon('heroicon-o-document-duplicate')
                    ->requiresConfirmation()
                    ->modalDescription(__('Are you sure you want to duplicate this job?'))
                    ->deselectRecordsAfterCompletion()
                    ->visible(fn () => auth()->user()?->can('duplicate', new $modelClass) ?? true)
                    ->action(function (BulkAction $action, Collection $records) {
                        foreach ($records as $record) {
                            static::handle($action, $record);
                        }
                    });

                break;
            default:
                return Action::make('duplicate')
                    ->label(__('Duplicate Job'))
                    ->color('secondary')
                    ->icon('heroicon-o-document-duplicate')
                    ->requiresConfirmation()
                    ->modalDescription(__('Are you sure you want to duplicate this job?'))
                    ->visible(fn ($record) => auth()->user()?->can('duplicate', $record) ?? true)
                    ->action(function (Action $action, ExactQueue $record, $livewire = null) {
                        return static::handle($action, $record, $livewire);
                    });

                break;
        }
    }

    public static function handle(Action | TableAction | BulkAction $action, ExactQueue $record, $livewire = null)
    {
        if (auth()->user() && !auth()->user()->can('duplicate', $record)) {
            Notification::make()
                ->title(__('Permission Denied'))
                ->body(__('You do not have permission to duplicate this job.'))
                ->danger()
                ->send();
            return;
        }

        $newRecord = $record->replicate();
        $newRecord->status = QueueStatusEnum::PENDING;
        $newRecord->response = null;
        $newRecord->save();

        if (! is_null($livewire)) {
            $livewire->refreshFormData([
                'status', 'response',
            ]);
        }

        Notification::make()
            ->title(__('Duplicated successfully'))
            ->body(__('The job has been duplicated and added to the queue.'))
            ->success()
            ->send();
    }
}
