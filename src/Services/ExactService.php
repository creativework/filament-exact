<?php

namespace CreativeWork\FilamentExact\Services;

use CreativeWork\FilamentExact\Contracts\HttpClientInterface;
use CreativeWork\FilamentExact\Endpoints\Items;
use CreativeWork\FilamentExact\Endpoints\Users;
use CreativeWork\FilamentExact\Endpoints\Webhooks;

class ExactService implements ExactContract
{
    public function __construct(
        protected HttpClientInterface $client,
        protected ExactTokenService $tokenService,
    ) {}

    public function items(): Items
    {
        return $this->getEndpoint(Items::class);
    }

    public function users(): Users
    {
        return $this->getEndpoint(Users::class);
    }

    public function webhooks(): Webhooks
    {
        return $this->getEndpoint(Webhooks::class);
    }

    protected function getEndpoint(string $class)
    {
        return new $class($this->client);
    }
}
