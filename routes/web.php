<?php

use creativework\FilamentExact\Controllers\ExactController;

// Get path from redirect url
$redirectPath = parse_url(config('filament-exact.exact.redirect_uri'), PHP_URL_PATH);
if ($redirectPath) {
    Route::get($redirectPath, [ExactController::class, 'callback'])->name('exact.callback');
}
