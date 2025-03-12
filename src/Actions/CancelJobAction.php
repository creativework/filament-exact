<?php

namespace CreativeWork\FilamentExact\Actions;

use CreativeWork\FilamentExact\Enums\QueueStatusEnum;
use CreativeWork\FilamentExact\Models\ExactQueue;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

class CancelJobAction
{
    public static function make($type = 'general'): Action|TableAction|BulkAction
    {
        switch ($type) {
            case 'table':
                return TableAction::make('cancel')
                    ->label(__('Cancel Job'))
                    ->color('danger')
                    ->icon('heroicon-o-no-symbol')
                    ->requiresConfirmation()
                    ->modalDescription(__('Are you sure you want to cancel this job?'))
                    ->visible(fn ($record) => $record->status === QueueStatusEnum::PENDING)
                    ->action(function (TableAction $action, ExactQueue $record) {
                        return static::handle($action, $record);
                    });
                break;
            case 'bulk':
                return BulkAction::make('cancel')
                    ->label(__('Cancel Job'))
                    ->color('danger')
                    ->icon('heroicon-o-no-symbol')
                    ->requiresConfirmation()
                    ->modalDescription(__('Are you sure you want to cancel this job?'))
                    ->deselectRecordsAfterCompletion()
                    ->action(function (BulkAction $action, Collection $records) {
                        foreach ($records as $record) {
                            static::handle($action, $record);
                        }
                    });
                break;
            default:
                return Action::make('cancel')
                    ->label(__('Cancel Job'))
                    ->color('danger')
                    ->icon('heroicon-o-no-symbol')
                    ->requiresConfirmation()
                    ->modalDescription(__('Are you sure you want to cancel this job?'))
                    ->visible(fn ($record) => $record->status === QueueStatusEnum::PENDING)
                    ->action(function (Action $action, ExactQueue $record, $livewire = null) {
                        return static::handle($action, $record, $livewire);
                    });
                break;
        }
    }

    public static function handle(Action|TableAction|BulkAction $action, ExactQueue $record, $livewire = null) {
        $record->update([
            'status' => QueueStatusEnum::FAILED,
            'response' => 'Job has been cancelled by ' . auth()->user()->name,
        ]);

        if (! is_null($livewire)) {
            $livewire->refreshFormData([
                'status', 'response'
            ]);
        }

        Notification::make()
            ->title(__('Cancelled successfully'))
            ->body(__('The job has been cancelled and will not be dispatched.'))
            ->success()
            ->send();
    }
}
