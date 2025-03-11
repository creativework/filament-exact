<?php

namespace creativework\FilamentExact\Resources;

use creativework\FilamentExact\Enums\QueueStatusEnum;
use creativework\FilamentExact\Resources\ExactQueueResource\Pages\ListExactQueue;
use creativework\FilamentExact\Resources\ExactQueueResource\Pages\ViewExactQueue;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExactQueueResource extends Resource
{
    protected static ?string $slug = 'exact';

    protected static bool $isScopedToTenant = false;

    protected static bool $shouldRegisterNavigation = true;

    public static function getModel(): string
    {
        return config('filament-exact.model');
    }

    public static function getNavigationGroup(): ?string
    {
        return config('filament-exact.navigation.grouo', __('Exact'));
    }

    public static function getNavigationLabel(): string
    {
        return __('Exact') . ' ' . __('Queue');
    }

    public static function getLabel(): ?string
    {
        return __('Exact') . ' ' . __('Queue');
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-queue-list';
    }

    public function getTitle(): string
    {
        return __('Exact') . ' ' . __('Queue');
    }

    public static function getModelLabel(): string
    {
        return __('Job');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Jobs');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordAction('view')
            ->recordUrl(null)
            ->defaultSort('created_at', 'desc')
            ->paginated(50, 100, 'all')
            ->columns([
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->sortable()
                    ->searchable()
                    ->badge(),
                TextColumn::make('id')
                    ->label(__('Number'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('job')
                    ->label(__('Job'))
                    ->searchable(),
                TextColumn::make('parameters')
                    ->label(__('Parameters'))
                    ->searchable(),
                TextColumn::make('priority')
                    ->label(__('Priority'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([])
            ->actions([
                Action::make('prioritize')
                    ->label(__('Increase Priority'))
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalDescription(__('Are you sure you want to increase the priority of this job?'))
                    ->visible(fn ($record) => $record->status === QueueStatusEnum::PENDING)
                    ->action(function ($record) {
                        $record->update(['priority' => 10]);

                        Notification::make()
                            ->title(__('Priority increased'))
                            ->body(__('The task will be dispatched soon.'))
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExactQueue::route('/'),
            'view' => ViewExactQueue::route('/{record}/view'),
        ];
    }
}
