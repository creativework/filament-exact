<?php

namespace creativework\FilamentExact\Services;

use creativework\FilamentExact\Models\ExactToken;
use Picqer\Financials\Exact\Connection;

class ExactService
{
    protected Connection $connection;

    public function __construct()
    {
        $this->connection = new Connection;
        $this->connection->setRedirectUrl(config('filament-exact.exact.redirect_uri'));
        $this->connection->setExactClientId(config('filament-exact.exact.client_id'));
        $this->connection->setExactClientSecret(config('filament-exact.exact.client_secret'));
        $this->connection->setWaitOnMinutelyRateLimitHit(true);

        if (config('filament-exact.exact.division')) {
            $this->connection->setDivision(config('filament-exact.exact.division'));
        }

        if ($this->getValue('access_token')) {
            $this->connection->setAccessToken($this->getValue('access_token'));
        }

        if ($this->getValue('refresh_token')) {
            $this->connection->setRefreshToken($this->getValue('refresh_token'));
        }

        if ($this->getValue('expires_in')) {
            $this->connection->setTokenExpires($this->getValue('expires_in'));
        }
    }

    public function getAuthUrl()
    {
        return $this->connection->getAuthUrl();
    }

    public function authorize($code)
    {
        $this->setValue('authorization_code', $code);

        try {
            $this->connection->setAuthorizationCode($code);
            $this->connect();
        } catch (\Exception $e) {
            throw new \Exception('Could not authorize Exact: ' . $e->getMessage());
        }
    }

    public function connect()
    {
        try {
            $this->connection->connect();
        } catch (\Exception $e) {
            throw new \Exception('Could not connect to Exact: ' . $e->getMessage());
        }

        $this->setValue('client_id', config('filament-exact.exact.client_id'));
        $this->setValue('access_token', serialize($this->connection->getAccessToken()));
        $this->setValue('refresh_token', $this->connection->getRefreshToken());
        $this->setValue('expires_in', $this->connection->getTokenExpires());
    }

    private function getValue(string $key)
    {
        $token = ExactToken::first();

        return $token ? $token[$key] : null;
    }

    private function setValue(string $key, $value)
    {
        $token = ExactToken::first();
        if ($token) {
            $token[$key] = $value;
            $token->save();
        } else {
            ExactToken::create([$key => $value]);
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function setDivision($division)
    {
        $this->connection->setDivision($division);
    }
}
