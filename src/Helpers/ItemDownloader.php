<?php

namespace CreativeWork\FilamentExact\Helpers;

use CreativeWork\FilamentExact\Services\ExactService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Log;
use Picqer\Financials\Exact\Item;
use Psr\Http\Message\StreamInterface;

class ItemDownloader
{
    public function download(Item $item): ?StreamInterface
    {
        try {
            $exactService = new ExactService();
            $connection = $exactService->refresh();

            $client = new Client();
            $headers = [
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
                'Prefer'        => 'return=representation',
                'Authorization' => 'Bearer ' . $connection->getAccessToken(),
            ];

            $res = $client->get($item->getDownloadUrl(), [
                'headers' => $headers,
            ]);

            return $res->getBody();
        } catch (RequestException $e) {
            Log::warning("Failed to download item image [{$item->ID}]: {$e->getMessage()}");
            return null;
        }
    }
}
