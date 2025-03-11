<?php

use creativework\FilamentExact\Controllers\ExactController;

Route::get(config('filament-exact.exact.callback_path'), [ExactController::class, 'callback'])->name('exact.callback');
