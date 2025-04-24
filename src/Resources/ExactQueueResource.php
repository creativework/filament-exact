<?php

namespace CreativeWork\FilamentExact\Resources;

use CreativeWork\FilamentExact\Enums\QueueStatusEnum;
use CreativeWork\FilamentExact\Resources\ExactQueueResource\Pages\ListExactQueue;
use CreativeWork\FilamentExact\Resources\ExactQueueResource\Pages\ViewExactQueue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
        return config('filament-exact.navigation.group', __('Exact'));
    }

    public static function getNavigationSort(): ?int
    {
        return config('filament-exact.navigation.sort', -1);
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
        return __('jobs');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Job Details'))
                    ->description(__('Details about the job.'))
                    ->schema([
                        TextInput::make('id')
                            ->label(__('Number'))
                            ->columnSpan(1)
                            ->disabled(),
                        TextInput::make('job')
                            ->label(__('Job'))
                            ->columnSpan(1)
                            ->disabled(),
                        Select::make('status')
                            ->label(__('Status'))
                            ->options(QueueStatusEnum::class)
                            ->columnSpan(2)
                            ->disabled(),
                        TextInput::make('priority')
                            ->label(__('Priority'))
                            ->columnSpan(2)
                            ->disabled(),
                        Textarea::make('parameters')
                            ->label(__('Parameters'))
                            ->formatStateUsing(fn ($state) => json_encode($state, JSON_THROW_ON_ERROR))
                            ->columnSpan(2)
                            ->disabled(),
                        TextArea::make('response')
                            ->label(__('Response'))
                            ->columnSpan(2)
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->deferLoading()
            ->recordUrl(fn ($record) => static::getUrl('view', ['record' => $record]))
            ->defaultSort(fn (Builder $query): Builder => $query->orderBy('priority', 'desc')->orderBy('id', 'asc'))
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
            ->actions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExactQueue::route('/'),
            'view' => ViewExactQueue::route('/{record}'),
        ];
    }
}
