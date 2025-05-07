<?php

use CreativeWork\FilamentExact\Models\ExactQueue;
use CreativeWork\FilamentExact\Resources\ExactQueueResource;

return [
    'model' => ExactQueue::class,
    'resource' => ExactQueueResource::class,
    'database' => [
        'tables' => [
            'queue' => 'exact_queue',
            'tokens' => 'exact_tokens',
        ],
        'pruning' => [
            'after' => 30, // days
        ],
    ],

    'notifications' => [
        'mail' => [
            'to' => [],
        ],
    ],

    'exact' => [
        'redirect_uri' => env('EXACT_ONLINE_REDIRECT_URI'),
        'client_id' => env('EXACT_ONLINE_CLIENT_ID'),
        'client_secret' => env('EXACT_ONLINE_CLIENT_SECRET'),
        'division' => env('EXACT_ONLINE_DIVISION'),
        'webhook_secret' => env('EXACT_ONLINE_WEBHOOK_SECRET'),
        'webhook_uri' => env('EXACT_ONLINE_WEBHOOK_URI'),
    ],

    'navigation' => [
        'group' => null,
        'sort' => -1,
    ],
];
