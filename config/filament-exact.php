<?php

use creativework\FilamentExact\Models\ExactQueue;
use creativework\FilamentExact\Resources\ExactQueueResource;

return [
    'model' => ExactQueue::class,
    'resource' => ExactQueueResource::class,
    'database' => [
        'tables' => [
            'queue' => 'exact_queue',
            'tokens' => 'exact_tokens',
        ],
        'pruning' => [
            'enabled' => true,
            'after' => 30, // days
        ],
    ],

    'notifications' => [
        'mail' => [
            'to' => ['test@example.com'],
        ],
    ],

    'exact' => [
        'redirect_uri' => '',
        'callback_path' => 'exact/callback',
        'client_id' => '',
        'client_secret' => '',
        'division' => '',
        'webhook_secret' => '',
    ],

    'navigation' => [
        'group' => null,
    ],
];
