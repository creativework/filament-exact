<?php

namespace CreativeWork\FilamentExact\Contracts;

interface HttpClientInterface
{
    public function get(string $uri, array $params = [], array $headers = []): array;

    public function post(string $uri, array $params = [], array $data = [], array $headers = []): array;

    public function put(string $uri, array $params = [], array $data = [], array $headers = []): array;

    public function delete(string $uri, array $params = [], array $headers = []): array;
}
