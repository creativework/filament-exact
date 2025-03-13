<?php

use CreativeWork\FilamentExact\Controllers\ExactController;
use CreativeWork\FilamentExact\Controllers\ExactWebhookController;

// Get path from redirect url
$redirectPath = parse_url(config('filament-exact.exact.redirect_uri'), PHP_URL_PATH);
if ($redirectPath) {
    Route::get($redirectPath, [ExactController::class, 'callback'])->name('exact.callback');
}

// Get path from webhook url
$webhookPath = parse_url(config('filament-exact.exact.webhook_uri'), PHP_URL_PATH);
if ($webhookPath) {
    Route::post($webhookPath, [ExactWebhookController::class, 'handle'])->name('exact.webhooks.handle');
}
