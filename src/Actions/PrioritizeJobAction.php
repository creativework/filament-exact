<?php

namespace CreativeWork\FilamentExact\Actions;

use CreativeWork\FilamentExact\Enums\QueuePriorityEnum;
use CreativeWork\FilamentExact\Enums\QueueStatusEnum;
use CreativeWork\FilamentExact\Models\ExactQueue;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

class PrioritizeJobAction
{
    public static function make($type = 'general'): Action | TableAction | BulkAction
    {
        $modelClass = config('filament-exact.model');

        switch ($type) {
            case 'table':
                return TableAction::make('prioritize')
                    ->label(__('Increase Priority'))
                    ->color('primary')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->requiresConfirmation()
                    ->modalDescription(__('Are you sure you want to increase the priority of this job?'))
                    ->visible(fn ($record) => $record->status === QueueStatusEnum::PENDING && auth()->user()?->can('prioritize', $record) ?? true)
                    ->action(function (TableAction $action, ExactQueue $record) {
                        return static::handle($action, $record);
                    });

                break;
            case 'bulk':
                return BulkAction::make('prioritize')
                    ->label(__('Increase Priority'))
                    ->color('primary')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->requiresConfirmation()
                    ->modalDescription(__('Are you sure you want to increase the priority of this job?'))
                    ->deselectRecordsAfterCompletion()
                    ->visible(fn () => auth()->user()?->can('prioritize', new $modelClass) ?? true)
                    ->action(function (BulkAction $action, Collection $records) {
                        foreach ($records as $record) {
                            static::handle($action, $record);
                        }
                    });

                break;
            default:
                return Action::make('prioritize')
                    ->label(__('Increase Priority'))
                    ->color('primary')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->requiresConfirmation()
                    ->modalDescription(__('Are you sure you want to increase the priority of this job?'))
                    ->visible(fn ($record) => $record->status === QueueStatusEnum::PENDING && auth()->user()?->can('prioritize', $record) ?? true)
                    ->action(function (Action $action, ExactQueue $record, $livewire = null) {
                        return static::handle($action, $record, $livewire);
                    });

                break;
        }
    }

    public static function handle(Action | TableAction | BulkAction $action, ExactQueue $record, $livewire = null)
    {
        if (auth()->user() && ! auth()->user()->can('prioritize', $record)) {
            Notification::make()
                ->title(__('Permission Denied'))
                ->body(__('You do not have permission to prioritize this job.'))
                ->danger()
                ->send();

            return;
        }

        $record->update(['priority' => QueuePriorityEnum::URGENT]);

        if (! is_null($livewire)) {
            $livewire->refreshFormData([
                'priority',
            ]);
        }

        Notification::make()
            ->title(__('Priority increased'))
            ->body(__('The task will be dispatched soon.'))
            ->success()
            ->send();
    }
}
