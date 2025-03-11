<?php

namespace creativework\FilamentExact\Facades;

use Illuminate\Support\Facades\Facade;

class FilamentExact extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \creativework\FilamentExact\FilamentExact::class;
    }
}
