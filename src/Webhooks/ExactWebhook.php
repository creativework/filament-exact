<?php

namespace CreativeWork\FilamentExact\Webhooks;

use Picqer\Financials\Exact\Webhook\Authenticatable;

abstract class ExactWebhook
{
    use Authenticatable;

    /**
     * ExactOnline topic (e.g. salesorder, salesinvoice, etc.)
     */
    public string $topic;

    /**
     * ExactOnline webhook slug
     */
    public string $slug;

    abstract public function handle(array $body): void;
}
