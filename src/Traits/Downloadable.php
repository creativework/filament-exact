<?php

namespace CreativeWork\FilamentExact\Traits;

use CreativeWork\FilamentExact\Services\Connection;
use GuzzleHttp\Client;
use Psr\Http\Message\StreamInterface;

trait Downloadable
{
    abstract public function connection(): Connection;

    abstract public function getDownloadUrl(): string;

    public function download(): StreamInterface
    {
        $client = new Client();

        $headers = [
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Prefer'        => 'return=representation',
            'Authorization' => 'Bearer ' . $this->connection()->getAccessToken(),
        ];

        $res = $client->get($this->getDownloadUrl(), [
            'headers' => $headers,
        ]);

        return $res->getBody();
    }
}
