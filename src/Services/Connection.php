<?php

namespace CreativeWork\FilamentExact\Services;

use CreativeWork\FilamentExact\Endpoints\Me;
use CreativeWork\FilamentExact\Endpoints\SystemUser;
use CreativeWork\FilamentExact\Exceptions\ApiException;
use CreativeWork\FilamentExact\Models\ExactToken;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

class Connection
{
    private string $baseUrl = 'https://start.exactonline.nl';

    private string $apiUrl = '/api/v1';

    private string $authUrl = '/api/oauth2/auth';

    private string $tokenUrl = '/api/oauth2/token';

    /**
     * @var mixed
     */
    private $exactClientId;

    /**
     * @var mixed
     */
    private $exactClientSecret;

    /**
     * @var mixed
     */
    private $redirectUrl;

    private bool $forceLogin = false;

    private ?string $state = null;

    /**
     * @var mixed
     */
    private $division;

    private ?Client $client = null;

    /**
     * @var callable(Connection): void|null
     */
    private $tokenUpdateCallback;

    /**
     * @var callable(Connection): void|null
     */
    private $acquireAccessTokenLockCallback;

    /**
     * @var callable(Connection): void|null
     */
    private $acquireAccessTokenUnlockCallback;

    /**
     * @var callable(Connection): void|null
     */
    private $refreshAccessTokenCallback;

    /**
     * @var list<callable(callable): callable>
     */
    protected array $middleWares = [];

    public ?string $nextUrl = null;

    protected ?int $dailyLimit = null;

    protected ?int $dailyLimitRemaining = null;

    protected ?int $dailyLimitReset = null;

    protected ?int $minutelyLimit = null;

    protected ?int $minutelyLimitRemaining = null;

    protected ?int $minutelyLimitReset = null;

    private bool $waitOnMinutelyRateLimitHit = false;

    private function client(): Client
    {
        if ($this->client) {
            return $this->client;
        }

        $handlerStack = HandlerStack::create();
        foreach ($this->middleWares as $middleWare) {
            $handlerStack->push($middleWare);
        }

        $this->client = new Client([
            'http_errors' => true,
            'handler' => $handlerStack,
            'expect' => false,
        ]);

        return $this->client;
    }

    /**
     * Insert a custom Guzzle client.
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    /**
     * Insert a Middleware for the Guzzle-Client.
     *
     * @param  callable  $middleWare
     */
    public function insertMiddleWare($middleWare)
    {
        $this->middleWares[] = $middleWare;
    }

    /**
     * @throws ApiException
     */
    public function connect(): Client
    {
        // Redirect for authorization if needed (no access token or refresh token given)
        if ($this->needsAuthentication()) {
            $this->redirectForAuthorization();
        }

        $this->checkOrAcquireAccessToken();

        return $this->client();
    }

    /**
     * Checks whether the access token is still valid.
     *
     * @throws ApiException
     */
    public function checkOrAcquireAccessToken(): void
    {
        // If access token is not set or token has expired, acquire new token
        if (empty($this->getAccessToken()) || $this->tokenHasExpired()) {
            $this->acquireAccessToken();
        }
    }

    /**
     * @param  string  $method
     * @param  string  $endpoint
     * @param  mixed  $body
     *
     * @throws ApiException
     */
    private function createRequest($method, $endpoint, $body = null, array $params = [], array $headers = []): Request
    {
        // Add default json headers to the request
        $headers = array_merge($headers, [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]);

        $this->checkOrAcquireAccessToken();

        // If we have a token, sign the request
        $accessToken = $this->getAccessToken();
        if (! $this->needsAuthentication() && ! empty($accessToken)) {
            $headers['Authorization'] = 'Bearer ' . $accessToken;
        }

        // Create param string
        if (! empty($params)) {
            $endpoint .= strpos($endpoint, '?') === false ? '?' : '&';
            $endpoint .= http_build_query($params);
        }

        // Create the request
        return new Request($method, $endpoint, $headers, $body);
    }

    /**
     * @param  string  $url
     * @return mixed
     *
     * @throws ApiException
     */
    public function get($url, array $params = [], array $headers = [])
    {
        $this->waitIfMinutelyRateLimitHit();
        $url = $this->formatUrl($url, $this->requiresDivisionInRequestUrl($url), $url === $this->nextUrl);

        try {
            $request = $this->createRequest('GET', $url, null, $params, $headers);
            $response = $this->client()->send($request);

            return $this->parseResponse($response, $url != $this->nextUrl);
        } catch (Exception $e) {
            $this->parseExceptionForErrorMessages($e);
        }
    }

    /**
     * @param  string  $url
     * @param  mixed  $body
     * @return mixed
     *
     * @throws ApiException
     */
    public function post($url, $body)
    {
        $this->waitIfMinutelyRateLimitHit();
        $url = $this->formatUrl($url);

        try {
            $request = $this->createRequest('POST', $url, $body);
            $response = $this->client()->send($request);

            return $this->parseResponse($response);
        } catch (Exception $e) {
            $this->parseExceptionForErrorMessages($e);
        }
    }

    /**
     * @param  string  $topic
     * @param  mixed  $body
     * @param  array  $params
     * @return mixed
     *
     * @throws ApiException
     */
    public function upload($topic, $body, $params = [])
    {
        $url = $this->getBaseUrl() . '/docs/XMLUpload.aspx?Topic=' . $topic . '&_Division_=' . $this->getDivision();

        try {
            $request = $this->createRequest('POST', $url, $body, $params);
            $response = $this->client()->send($request);

            return $this->parseResponseXml($response);
        } catch (Exception $e) {
            $this->parseExceptionForErrorMessages($e);
        }
    }

    /**
     * @param  string  $topic
     * @param  mixed  $params
     * @return mixed
     *
     * @throws ApiException
     */
    public function download($topic, $params = [])
    {
        $url = $this->getBaseUrl() . '/docs/XMLDownload.aspx?Topic=' . $topic . '&_Division_=' . $this->getDivision();

        try {
            $request = $this->createRequest('GET', $url, null, $params);
            $response = $this->client()->send($request);

            return $this->parseDownloadResponseXml($response);
        } catch (Exception $e) {
            $this->parseExceptionForErrorMessages($e);
        }
    }

    /**
     * @param  string  $url
     * @param  mixed  $body
     * @return mixed
     *
     * @throws ApiException
     */
    public function put($url, $body)
    {
        $this->waitIfMinutelyRateLimitHit();
        $url = $this->formatUrl($url);

        try {
            $request = $this->createRequest('PUT', $url, $body);
            $response = $this->client()->send($request);

            return $this->parseResponse($response);
        } catch (Exception $e) {
            $this->parseExceptionForErrorMessages($e);
        }
    }

    /**
     * @param  string  $url
     * @return mixed
     *
     * @throws ApiException
     */
    public function delete($url)
    {
        $this->waitIfMinutelyRateLimitHit();
        $url = $this->formatUrl($url);

        try {
            $request = $this->createRequest('DELETE', $url);
            $response = $this->client()->send($request);

            return $this->parseResponse($response);
        } catch (Exception $e) {
            $this->parseExceptionForErrorMessages($e);
        }
    }

    public function getAuthUrl(): string
    {
        return $this->baseUrl . $this->authUrl . '?' . http_build_query([
            'client_id' => $this->exactClientId,
            'redirect_uri' => $this->redirectUrl,
            'response_type' => 'code',
            'state' => $this->state,
            'force_login' => $this->forceLogin ? 1 : 0,
        ]);
    }

    /**
     * @param  mixed  $exactClientId
     */
    public function setExactClientId($exactClientId)
    {
        $this->exactClientId = $exactClientId;
    }

    /**
     * @param  mixed  $exactClientSecret
     */
    public function setExactClientSecret($exactClientSecret)
    {
        $this->exactClientSecret = $exactClientSecret;
    }

    /**
     * @param  mixed  $authorizationCode
     */
    public function setAuthorizationCode($authorizationCode)
    {
        $token = ExactToken::firstOrNew([]);
        $token->authorization_code = $authorizationCode;
        $token->save();
    }

    /**
     * @param  mixed  $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $token = ExactToken::firstOrNew([]);
        $token->access_token = $accessToken;
        $token->save();
    }

    /**
     * @param  mixed  $refreshToken
     */
    public function setRefreshToken($refreshToken)
    {
        $token = ExactToken::firstOrNew([]);
        $token->refresh_token = $refreshToken;
        $token->save();
    }

    public function redirectForAuthorization(): void
    {
        $authUrl = $this->getAuthUrl();
        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * @param  mixed  $redirectUrl
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }

    public function setForceLogin(bool $forceLogin): void
    {
        $this->forceLogin = $forceLogin;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function needsAuthentication(): bool
    {
        $token = ExactToken::firstOrNew([]);
        if (! $token) {
            return true;
        }

        return empty($token->refresh_token) && empty($token->authorization_code);
    }

    /**
     * @return mixed
     *
     * @throws ApiException
     */
    private function parseResponse(ResponseInterface $response, bool $returnSingleIfPossible = true)
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
                    if ($returnSingleIfPossible && count($json['d']['results']) == 1) {
                        return $json['d']['results'][0];
                    }

                    return $json['d']['results'];
                }

                return $json['d'];
            }

            return $json;
        } catch (\RuntimeException $e) {
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * @return mixed
     *
     * @throws ApiException
     */
    private function parseResponseXml(ResponseInterface $response)
    {
        try {
            if ($response->getStatusCode() === 204) {
                return [];
            }

            $answer = [];
            Psr7\Message::rewindBody($response);
            $simpleXml = new \SimpleXMLElement($response->getBody()->getContents());

            foreach ($simpleXml->Messages->Message as $message) {
                if ($message->Topic->Data->attributes() === null) {
                    $answer[] = (string) $message->Description;
                } else {
                    $keyAlt = (string) $message->Topic->Data->attributes()['keyAlt'];
                    $answer[$keyAlt] = (string) $message->Description;
                }
            }

            return $answer;
        } catch (\RuntimeException $e) {
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * @return mixed
     *
     * @throws ApiException
     */
    private function parseDownloadResponseXml(ResponseInterface $response)
    {
        try {
            if ($response->getStatusCode() === 204) {
                return [];
            }

            $simpleXml = new \SimpleXMLElement($response->getBody()->__toString());

            return $simpleXml;
        } catch (\RuntimeException $e) {
            throw new ApiException($e->getMessage());
        } catch (Exception $e) {
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    private function getCurrentDivisionNumber()
    {
        if (empty($this->division)) {
            $me = new Me($this);
            $this->division = $me->find()->CurrentDivision;
        }

        return $this->division;
    }

    /**
     * @return mixed
     */
    public function getRefreshToken()
    {
        $token = ExactToken::firstOrNew([]);
        if ($token && $token->refresh_token) {
            return $token->refresh_token;
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        $token = ExactToken::firstOrNew([]);
        if ($token && $token->access_token) {
            return $token->access_token;
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getAuthorizationCode()
    {
        $token = ExactToken::firstOrNew([]);
        if ($token && $token->authorization_code) {
            return $token->authorization_code;
        }

        return null;
    }

    private function acquireAccessToken(): void
    {
        try {
            if (is_callable($this->acquireAccessTokenLockCallback)) {
                call_user_func($this->acquireAccessTokenLockCallback, $this);
            }

            if (is_callable($this->refreshAccessTokenCallback)) {
                call_user_func($this->refreshAccessTokenCallback, $this);
                if (! $this->tokenHasExpired()) {
                    // the refreshed token has not expired, so we are fine to keep using it
                    return;
                }
            }

            // If refresh token not yet acquired, do token request
            if (empty($this->getRefreshToken())) {
                $body = [
                    'form_params' => [
                        'redirect_uri' => $this->redirectUrl,
                        'grant_type' => 'authorization_code',
                        'client_id' => $this->exactClientId,
                        'client_secret' => $this->exactClientSecret,
                        'code' => $this->getAuthorizationCode(),
                    ],
                ];
            } else { // else do refresh token request
                $body = [
                    'form_params' => [
                        'refresh_token' => $this->getRefreshToken(),
                        'grant_type' => 'refresh_token',
                        'client_id' => $this->exactClientId,
                        'client_secret' => $this->exactClientSecret,
                    ],
                ];
            }

            $response = $this->client()->post($this->getTokenUrl(), $body);

            Psr7\Message::rewindBody($response);
            $responseBody = $response->getBody()->getContents();
            $body = json_decode($responseBody, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->setAccessToken($body['access_token']);
                $this->setRefreshToken($body['refresh_token']);
                $this->setTokenExpires($this->getTimestampFromExpiresIn($body['expires_in']));

                if (is_callable($this->tokenUpdateCallback)) {
                    call_user_func($this->tokenUpdateCallback, $this);
                }
            } else {
                throw new ApiException('Could not acquire tokens, json decode failed. Got response: ' . $responseBody);
            }
        } catch (BadResponseException $ex) {
            $this->parseExceptionForErrorMessages($ex);
        } finally {
            if (is_callable($this->acquireAccessTokenUnlockCallback)) {
                call_user_func($this->acquireAccessTokenUnlockCallback, $this);
            }
        }
    }

    /**
     * Translates expires_in to a Unix timestamp.
     *
     * @param  string  $expiresIn  number of seconds until the token expires
     */
    private function getTimestampFromExpiresIn($expiresIn): int
    {
        if (! ctype_digit($expiresIn)) {
            throw new \InvalidArgumentException('Function requires a numeric expires value');
        }

        return time() + (int) $expiresIn;
    }

    public function getTokenExpires(): int
    {
        $token = ExactToken::firstOrNew([]);
        if ($token && $token->expires_in) {
            return $token->expires_in;
        }

        return 0;
    }

    /**
     * @param  int  $tokenExpires  the Unix timestamp at which the access token expires
     */
    public function setTokenExpires($tokenExpires)
    {
        $token = ExactToken::firstOrNew([]);
        $token->expires_in = $tokenExpires;
        $token->save();
    }

    private function tokenHasExpired(): bool
    {
        $token = ExactToken::firstOrNew([]);
        if ($token && $token->expires_in) {
            return ($token->expires_in - 10) < time();
        }

        return true;
    }

    private function formatUrl($endPoint, $includeDivision = true, $formatNextUrl = false)
    {
        if ($formatNextUrl) {
            return $endPoint;
        }

        if ($includeDivision) {
            return implode('/', [
                $this->getApiUrl(),
                $this->getCurrentDivisionNumber(),
                $endPoint,
            ]);
        }

        return implode('/', [
            $this->getApiUrl(),
            $endPoint,
        ]);
    }

    /**
     * @return mixed
     */
    public function getDivision()
    {
        return $this->division;
    }

    /**
     * @param  mixed  $division
     */
    public function setDivision($division)
    {
        $this->division = $division;
    }

    /**
     * @param  callable  $callback
     */
    public function setAcquireAccessTokenLockCallback($callback): void
    {
        $this->acquireAccessTokenLockCallback = $callback;
    }

    /**
     * @param  callable  $callback
     */
    public function setAcquireAccessTokenUnlockCallback($callback): void
    {
        $this->acquireAccessTokenUnlockCallback = $callback;
    }

    /**
     * @param  callable  $callback
     */
    public function setTokenUpdateCallback($callback): void
    {
        $this->tokenUpdateCallback = $callback;
    }

    /**
     * @param  callable  $callback
     */
    public function setRefreshAccessTokenCallback($callback): void
    {
        $this->refreshAccessTokenCallback = $callback;
    }

    /**
     * Parse the reponse in the Exception to return the Exact error messages.
     *
     *
     * @throws ApiException
     */
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

    /**
     * Return the maximum number of API calls that your app is permitted to make per company, per day.
     */
    public function getDailyLimit(): ?int
    {
        return $this->dailyLimit;
    }

    /**
     * Return the remaining number of API calls that your app is permitted to make for a company, per day.
     */
    public function getDailyLimitRemaining(): ?int
    {
        return $this->dailyLimitRemaining;
    }

    /**
     * Return the time at which the rate limit window resets in UTC epoch milliseconds.
     */
    public function getDailyLimitReset(): ?int
    {
        return $this->dailyLimitReset;
    }

    /**
     * Return the maximum number of API calls that your app is permitted to make per company, per minute.
     */
    public function getMinutelyLimit(): ?int
    {
        return $this->minutelyLimit;
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

    protected function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    private function getApiUrl(): string
    {
        return $this->baseUrl . $this->apiUrl;
    }

    private function getTokenUrl(): string
    {
        return $this->baseUrl . $this->tokenUrl;
    }

    /**
     * Set base URL for different countries according to
     * https://developers.exactonline.com/#Exact%20Online%20sites.html.
     */
    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }

    public function setApiUrl(string $apiUrl): void
    {
        $this->apiUrl = $apiUrl;
    }

    public function setAuthUrl(string $authUrl): void
    {
        $this->authUrl = $authUrl;
    }

    public function setTokenUrl(string $tokenUrl): void
    {
        $this->tokenUrl = $tokenUrl;
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

    protected function waitIfMinutelyRateLimitHit(): void
    {
        if (! $this->waitOnMinutelyRateLimitHit) {
            return;
        }

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

    public function setWaitOnMinutelyRateLimitHit(bool $waitOnMinutelyRateLimitHit): void
    {
        $this->waitOnMinutelyRateLimitHit = $waitOnMinutelyRateLimitHit;
    }

    private function requiresDivisionInRequestUrl(string $endpointUrl): bool
    {
        return ! in_array($endpointUrl, [
            (new SystemUser($this))->url(),
            (new Me($this))->url(),
        ], true);
    }
}
