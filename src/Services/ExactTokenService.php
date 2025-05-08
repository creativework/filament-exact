<?php

namespace CreativeWork\FilamentExact\Services;

use CreativeWork\FilamentExact\Models\ExactToken;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;

class ExactTokenService
{
    protected Client $client;

    protected string $clientId;

    protected string $clientSecret;

    protected string $redirectUri;

    protected string $baseUrl = 'https://start.exactonline.nl';

    protected string $authUrl = '/api/oauth2/auth';

    protected string $tokenUrl = '/api/oauth2/token';

    public function __construct()
    {
        $this->client = new Client([
            'http_errors' => true,
            'expect' => false,
        ]);

        $this->clientId = config('filament-exact.exact.client_id');
        $this->clientSecret = config('filament-exact.exact.client_secret');
        $this->redirectUri = config('filament-exact.exact.redirect_uri');
    }

    public function getAccessToken(): string
    {
        $token = ExactToken::firstOrNew([]);
        if ($token && ! $token->isAlmostExpired()) {
            return $token->access_token;
        }

        return $this->refreshAccessToken();
    }

    public function refreshAccessToken(?string $code = null): string
    {
        $formParams = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];

        if ($code) {
            $formParams['grant_type'] = 'authorization_code';
            $formParams['code'] = $code;
            $formParams['redirect_uri'] = $this->redirectUri;
        } else {
            $token = ExactToken::firstOrNew([]);
            if (! $token || ! $token->refresh_token) {
                throw new \Exception('No refresh token available');
            }

            $formParams['grant_type'] = 'refresh_token';
            $formParams['refresh_token'] = $token->refresh_token;
        }

        $response = $this->client->post($this->baseUrl . $this->tokenUrl, [
            'form_params' => $formParams,
        ]);

        $body = json_decode($response->getBody()->getContents(), true);
        if (! isset($body['access_token'])) {
            throw new \Exception('No access token received');
        }

        ExactToken::updateOrCreate([
            'client_id' => $this->clientId,
        ], [
            'authorization_code' => $code,
            'access_token' => $body['access_token'],
            'refresh_token' => $body['refresh_token'] ?? null,
            'expires_in' => Carbon::now()->addSeconds((int) $body['expires_in']),
        ]);

        return $body['access_token'];
    }

    public function getAuthUrl(): string
    {
        return $this->baseUrl . $this->authUrl . '?' . http_build_query([
            'client_id' => config('filament-exact.exact.client_id'),
            'redirect_uri' => config('filament-exact.exact.redirect_uri'),
            'response_type' => 'code',
        ]);
    }
}
