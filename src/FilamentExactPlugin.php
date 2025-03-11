<?php

namespace creativework\FilamentExact;

use creativework\FilamentExact\Resources\ExactQueueResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentExactPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-exact';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                config('filament-exact.resource', ExactQueueResource::class),
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
