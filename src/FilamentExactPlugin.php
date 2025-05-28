<?php

namespace CreativeWork\FilamentExact;

use CreativeWork\FilamentExact\Policies\ExactQueuePolicy;
use CreativeWork\FilamentExact\Resources\ExactQueueResource;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

class FilamentExactPlugin implements Plugin
{
    protected array $webhooks = [];

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

    public function getId(): string
    {
        return 'filament-exact';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            config('filament-exact.resource', ExactQueueResource::class),
        ]);

        if (config('filament-exact.shield.enabled', false)) {
            $this->setupPermissions();
        }
    }

    public function boot(Panel $panel): void
    {
        //
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

    /**
     * Setup permissions and policy
     */
    protected function setupPermissions(): void
    {
        // Create permissions if spatie/laravel-permission is available
        $this->createPermissions();

        // Register the policy
        $this->registerPolicy();
    }

    /**
     * Create permissions for Filament Shield compatibility
     */
    protected function createPermissions(): void
    {
        if (! class_exists(Permission::class)) {
            return;
        }

        $permissions = config('filament-exact.shield.permissions', []);

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }

    /**
     * Register the policy
     */
    protected function registerPolicy(): void
    {
        Gate::policy(config('filament-exact.model'), ExactQueuePolicy::class);
    }

    /**
     * Check if permissions are enabled
     */
    public function hasPermissions(): bool
    {
        return config('filament-exact.shield.enabled', false);
    }

    /**
     * Get permission names from config
     */
    public function getPermissionNames(): array
    {
        return config('filament-exact.shield.permissions', []);
    }
}
