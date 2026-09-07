<?php

interface HttpClientInterface
{
    public function setHeaders(array $headers): void;

    public function setBearerToken($token): void;

    public function setCookie($cookieStr): void;

    public function get(): array;

    public function post($data): array;

    public function put($data): array;

    public function delete($data = null): array;

    public function patch($data = null): array;
}
