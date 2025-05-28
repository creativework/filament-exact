<?php

namespace CreativeWork\FilamentExact\Services;

use CreativeWork\FilamentExact\Models\ExactToken;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\StreamInterface;

class ExactService
{
    protected Connection $connection;

    protected ExactToken $token;

    public function __construct()
    {
        $this->connection = new Connection;

        // Setup connection with config values
        $this->connection->setRedirectUrl(config('filament-exact.exact.redirect_uri'));
        $this->connection->setExactClientId(config('filament-exact.exact.client_id'));
        $this->connection->setExactClientSecret(config('filament-exact.exact.client_secret'));
        $this->connection->setWaitOnMinutelyRateLimitHit(true);

        if (config('filament-exact.exact.division')) {
            $this->connection->setDivision(config('filament-exact.exact.division'));
        }
    }

    public function getAuthUrl()
    {
        return $this->connection->getAuthUrl();
    }

    public function authorize($code)
    {
        $this->connection->setAuthorizationCode($code);

        try {
            $this->connection->checkOrAcquireAccessToken();
        } catch (\Exception $e) {
            throw new \Exception('Could not authorize Exact: ' . $e->getMessage());
        }
    }

    public function connect(): Connection
    {
        // Refresh tokens if needed
        $this->connection->checkOrAcquireAccessToken();

        return $this->connection;
    }

    public function refresh(): Connection
    {
        if (! $this->tokenHasExpired()) {
            return $this->connection;
        }

        return $this->connect();
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function setDivision($division)
    {
        $this->connection->setDivision($division);
    }

    private function tokenHasExpired(): bool
    {
        if (empty($this->connection->getTokenExpires())) {
            return true;
        }

        return $this->connection->getTokenExpires() < time();
    }

    public function download(string $url): ?StreamInterface
    {
        try {
            $client = new Client;
            $res = $client->get($url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Prefer' => 'return=representation',
                    'Authorization' => 'Bearer ' . $this->connection->getAccessToken(),
                ],
            ]);

            return $res->getBody();
        } catch (RequestException $e) {
            Log::warning("Failed to download item image: {$e->getMessage()}");

            return null;
        }
    }
}
