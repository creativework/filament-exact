<?php

namespace CreativeWork\FilamentExact\Facades;

use Illuminate\Support\Facades\Facade;

class FilamentExact extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \CreativeWork\FilamentExact\FilamentExact::class;
    }
}
