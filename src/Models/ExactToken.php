<?php

namespace CreativeWork\FilamentExact\Models;

use Illuminate\Database\Eloquent\Model;

class ExactToken extends Model
{
    protected $fillable = [
        'locked',
    ];

    public function lock(): void
    {
        $this->update(['locked' => true]);
    }

    public function unlock(): void
    {
        $this->update(['locked' => false]);
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }
}
