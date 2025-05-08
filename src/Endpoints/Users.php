<?php

namespace CreativeWork\FilamentExact\Endpoints;

use CreativeWork\FilamentExact\Contracts\HttpClientInterface;

class Users
{
    public function __construct(
        protected HttpClientInterface $client,
    ) {}

    public function get(): array
    {
        return $this->client->get('logistics/Items');
    }
}
