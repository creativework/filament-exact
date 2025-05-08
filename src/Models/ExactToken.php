<?php

namespace CreativeWork\FilamentExact\Models;

use Illuminate\Database\Eloquent\Model;

class ExactToken extends Model
{
    protected $fillable = [
        'locked',
    ];

    protected function casts(): array
    {
        return [
            'expires_in' => 'datetime',
        ];
    }

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

    public function isAuthorized(): bool
    {
        return $this->access_token !== null && $this->refresh_token !== null;
    }

    public function isAlmostExpired(): bool
    {
        if (! $this->expires_in) {
            return false;
        }

        return $this->expires_in->isBefore(now());
    }
}
