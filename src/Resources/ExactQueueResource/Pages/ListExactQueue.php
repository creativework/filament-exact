<?php

namespace CreativeWork\FilamentExact\Resources\ExactQueueResource\Pages;

use CreativeWork\FilamentExact\Models\ExactToken;
use CreativeWork\FilamentExact\Resources\ExactQueueResource;
use CreativeWork\FilamentExact\Services\ExactTokenService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListExactQueue extends ListRecords
{
    public static function getResource(): string
    {
        return config('filament-exact.resource', ExactQueueResource::class);
    }

    public function getTitle(): string
    {
        return __('Exact') . ' ' . __('Queue');
    }

    protected function getActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        $class = config('filament-exact.model');

        $class = new $class;

        return [
            'pending' => Tab::make()
                ->label(__('Pending'))
                ->badgeColor('gray')
                ->icon('heroicon-o-inbox')
                ->badge($class::open()->count())
                ->modifyQueryUsing(function (Builder $query) use ($class): Builder {
                    return $class->open();
                }),
            'failed' => Tab::make()
                ->label(__('Failed'))
                ->badgeColor('danger')
                ->icon('heroicon-o-x-circle')
                ->badge($class::failed()->count())
                ->modifyQueryUsing(function (Builder $query) use ($class): Builder {
                    return $class->failed();
                }),
            'success' => Tab::make()
                ->label(__('Completed'))
                ->badgeColor('success')
                ->icon('heroicon-o-check-circle')
                ->badge($class::completed()->count())
                ->modifyQueryUsing(function (Builder $query) use ($class): Builder {
                    return $class->completed();
                }),
        ];
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('authorize')
                ->icon('heroicon-o-key')
                ->color(function ($record) {
                    $token = ExactToken::first();

                    return $token && $token['access_token'] ? 'danger' : 'success';
                })
                ->label(function ($record) {
                    $token = ExactToken::first();

                    return $token && $token['access_token'] ? __('Disconnect') : __('Authorize');
                })
                ->action(function ($record) {
                    $token = ExactToken::first();
                    if ($token && $token['access_token']) {
                        $token->delete();

                        Notification::make()
                            ->title(__('Disconnected'))
                            ->body(__('You have been disconnected from Exact'))
                            ->success()
                            ->send();

                        return;
                    }

                    $service = new ExactTokenService;
                    redirect()->away($service->getAuthUrl());
                }),
        ];
    }
}
