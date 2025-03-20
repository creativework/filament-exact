<?php

// Ensure package configuration is loaded
use CreativeWork\FilamentExact\FilamentExactServiceProvider;

it('loads the package configuration', function () {
    expect(config('filament-exact'))->toBeArray();
});

// Test if the service provider is registered
it('registers the FilamentExactServiceProvider', function () {
    $this->assertTrue(
        in_array(
            FilamentExactServiceProvider::class,
            app()->getLoadedProviders()
        )
    );
});
