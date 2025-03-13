<?php

namespace CreativeWork\FilamentExact;

use CreativeWork\FilamentExact\Resources\ExactQueueResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentExactPlugin implements Plugin
{

    protected array $webhooks = [];

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

    public function webhooks(array $webhooks): static
    {
        $this->webhooks = $webhooks;

        return $this;
    }

    public function getWebhooks(): array
    {
        return $this->webhooks;
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
