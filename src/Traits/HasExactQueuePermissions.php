<?php

namespace CreativeWork\FilamentExact\Traits;

trait HasExactQueuePermissions
{
    /**
     * Give user all ExactQueue permissions
     */
    public function giveExactQueuePermissions(): void
    {
        if (!method_exists($this, 'givePermissionTo')) {
            return;
        }

        $permissions = config('filament-exact.permissions.names', []);

        foreach ($permissions as $permission) {
            $this->givePermissionTo($permission);
        }
    }

    /**
     * Remove all ExactQueue permissions from user
     */
    public function revokeExactQueuePermissions(): void
    {
        if (!method_exists($this, 'revokePermissionTo')) {
            return;
        }

        $permissions = config('filament-exact.permissions.names', []);

        foreach ($permissions as $permission) {
            $this->revokePermissionTo($permission);
        }
    }
}
