<?php

namespace CreativeWork\FilamentExact\Endpoints;

use CreativeWork\FilamentExact\Contracts\HttpClientInterface;

class Webhooks
{
    public function __construct(
        protected HttpClientInterface $client,
    ) {}

    public function subscribe(string $topic, string $url): mixed
    {
        return $this->client->post('webhooks/WebhookSubscriptions', [
            'Topic' => $topic,
            'CallbackURL' => $url,
        ]);
    }
}
