<?php

namespace CreativeWork\FilamentExact\Endpoints;

use CreativeWork\FilamentExact\Contracts\HttpClientInterface;

class Items
{
    public function __construct(
        protected HttpClientInterface $client,
    ) {}

    public function get(): array
    {
        return $this->client->get('logistics/Items');
    }

    public function getPage(int $skip = 0, int $top = 100, string $filter = ''): array
    {
        return $this->client->get('logistics/Items', [
            '$skip' => $skip,
            '$top' => $top,
            '$filter' => $filter,
        ]);
    }

    public function download(string $url): ?string
    {
        return $this->client->download($url);
    }
}
