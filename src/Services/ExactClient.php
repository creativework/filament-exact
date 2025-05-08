<?php

namespace CreativeWork\FilamentExact\Services;

use CreativeWork\FilamentExact\Contracts\HttpClientInterface;
use CreativeWork\FilamentExact\Exceptions\ApiException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7;
use Psr\Http\Message\ResponseInterface;

class ExactClient implements HttpClientInterface
{
    protected Client $client;

    protected string $baseUrl = 'https://start.exactonline.nl';

    protected string $apiUrl = '/api/v1';

    public ?string $nextUrl = null;

    protected ?int $dailyLimit = null;

    protected ?int $dailyLimitRemaining = null;

    protected ?int $dailyLimitReset = null;

    protected ?int $minutelyLimit = null;

    protected ?int $minutelyLimitRemaining = null;

    protected ?int $minutelyLimitReset = null;

    public function __construct()
    {
        $this->client = new Client([
            'http_errors' => true,
            'expect' => false,
        ]);
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function get(string $uri, array $params = [], array $headers = []): array
    {
        return $this->request('GET', $uri, $params, $headers);
    }

    public function post(string $uri, array $params = [], array $data = [], array $headers = []): array
    {
        return $this->request('POST', $uri, $params, ['form_params' => $data], $headers);
    }

    public function put(string $uri, array $params = [], array $data = [], array $headers = []): array
    {
        return $this->request('PUT', $uri, $params, ['form_params' => $data], $headers);
    }

    public function delete(string $uri, array $params = [], array $headers = []): array
    {
        return $this->request('DELETE', $uri, $params, [], $headers);
    }

    public function download(string $url)
    {
        try {
            $client = new Client;
            $res = $client->get($url, [
                'headers' => $this->headers(),
            ]);

            return $res->getBody();
        } catch (Exception $e) {
            $this->parseExceptionForErrorMessages($e);
        }
    }

    private function request($method, $endpoint, array $params = [], array $headers = [])
    {
        $headers = array_merge($this->headers(), $headers);

        $this->waitIfMinutelyRateLimitHit();

        // Create param string
        if (! empty($params)) {
            $endpoint .= strpos($endpoint, '?') === false ? '?' : '&';
            $endpoint .= http_build_query($params);
        }

        $url = $this->formatUrl($endpoint);

        try {
            $response = $this->client->request($method, $url, array_merge($headers, [
                'headers' => $headers,
            ]));

            return $this->parseResponse($response);
        } catch (\Exception $e) {
            $this->parseExceptionForErrorMessages($e);
        }
    }

    private function headers(): array
    {
        $tokenService = new ExactTokenService;

        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Prefer' => 'return=representation',
            'Authorization' => 'Bearer ' . $tokenService->getAccessToken(),
        ];
    }

    protected function waitIfMinutelyRateLimitHit(): void
    {
        $minutelyReset = $this->getMinutelyLimitReset();

        if ($this->getMinutelyLimitRemaining() === 0 && $minutelyReset) {
            // add a second for rounding differences
            $resetsInSeconds = (($minutelyReset / 1000) - time()) + 1;

            // In some rare occasions the outcome of $resetsInSeconds computes into a value that is less than 0.
            // The sleep() method will in this case throw an exception.
            if ($resetsInSeconds < 0) {
                $resetsInSeconds = 0;
            }

            sleep($resetsInSeconds);
        }
    }

    /**
     * Return the remaining number of API calls that your app is permitted to make for a company, per minute.
     */
    public function getMinutelyLimitRemaining(): ?int
    {
        return $this->minutelyLimitRemaining;
    }

    /**
     * Return the time at which the minutely rate limit window resets in UTC epoch milliseconds.
     */
    public function getMinutelyLimitReset(): ?int
    {
        return $this->minutelyLimitReset;
    }

    private function formatUrl($endPoint)
    {
        $divison = config('filament-exact.exact.division');
        if ($divison) {
            return implode('/', [$this->getApiUrl(), config('filament-exact.exact.division'), $endPoint]);
        }

        return implode('/', [$this->getApiUrl(), $endPoint]);
    }

    private function getApiUrl(): string
    {
        return $this->baseUrl . $this->apiUrl;
    }

    private function parseResponse(ResponseInterface $response)
    {
        try {
            $this->extractRateLimits($response);

            if ($response->getStatusCode() === 204) {
                return [];
            }

            Psr7\Message::rewindBody($response);
            $responseBody = $response->getBody()->getContents();
            $json = json_decode($responseBody, true);
            if (is_array($json) === false) {
                throw new ApiException('Json decode failed. Got response: ' . $responseBody);
            }
            if (array_key_exists('d', $json)) {
                if (array_key_exists('__next', $json['d'])) {
                    $this->nextUrl = $json['d']['__next'];
                } else {
                    $this->nextUrl = null;
                }

                if (array_key_exists('results', $json['d'])) {
                    return $json['d']['results'];
                }

                return $json['d'];
            }

            return $json;
        } catch (\RuntimeException $e) {
            throw new ApiException($e->getMessage());
        }
    }

    private function extractRateLimits(ResponseInterface $response): void
    {
        $this->dailyLimit = (int) $response->getHeaderLine('X-RateLimit-Limit');
        $this->dailyLimitRemaining = (int) $response->getHeaderLine('X-RateLimit-Remaining');
        $this->dailyLimitReset = (int) $response->getHeaderLine('X-RateLimit-Reset');

        $this->minutelyLimit = (int) $response->getHeaderLine('X-RateLimit-Minutely-Limit');
        $this->minutelyLimitRemaining = (int) $response->getHeaderLine('X-RateLimit-Minutely-Remaining');
        $this->minutelyLimitReset = (int) $response->getHeaderLine('X-RateLimit-Minutely-Reset');
    }

    private function parseExceptionForErrorMessages(Exception $e): void
    {
        if (! $e instanceof BadResponseException) {
            throw new ApiException($e->getMessage(), 0, $e);
        }

        $response = $e->getResponse();

        $this->extractRateLimits($response);

        Psr7\Message::rewindBody($response);
        $responseBody = $response->getBody()->getContents();
        $decodedResponseBody = json_decode($responseBody, true);

        if (! is_null($decodedResponseBody) && isset($decodedResponseBody['error']['message']['value'])) {
            $errorMessage = $decodedResponseBody['error']['message']['value'];
        } else {
            $errorMessage = $responseBody;
        }

        if ($reason = $response->getHeaderLine('Reason')) {
            $errorMessage .= " (Reason: {$reason})";
        }

        throw new ApiException('Error ' . $response->getStatusCode() . ': ' . $errorMessage, $response->getStatusCode(), $e);
    }
}
