<?php

namespace CreativeWork\FilamentExact\Endpoints;

use CreativeWork\FilamentExact\Traits\Findable;
use CreativeWork\FilamentExact\Traits\Storable;

class WebhookSubscription extends Model
{
    use Findable;
    use Storable;

    protected $fillable = [
        'ID',
        'CallbackURL',
        'ClientID',
        'Created',
        'Creator',
        'CreatorFullName',
        'Description',
        'Division',
        'Topic',
        'UserID',
    ];

    protected $url = 'webhooks/WebhookSubscriptions';
}
