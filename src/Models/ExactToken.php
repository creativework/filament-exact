<?php

namespace creativework\FilamentExact\Models;

use Illuminate\Database\Eloquent\Model;

class ExactToken extends Model
{
    protected $fillable = [
        'status',
        'method',
        'parameters',
        'priority',
        'attempts',
        'response',
    ];
}
