<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

class Request
{
    private string $method;
    private string $uriPath;
    private array $queryParams;
    private array $parsedBody;
    private array $headers;

    public function __construct(string $method, string $uriPath, array $queryParams = [], array $parsedBody = [], array $headers = [])
    {
        $this->method = strtoupper($method);
        $this->uriPath = $uriPath;
        $this->queryParams = $queryParams;
        $this->parsedBody = $parsedBody;
        $this->headers = $headers;
    }

    public static function fromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $headers = self::fetchHeaders();
        $parsedBody = self::parseBody($headers);

        return new self($method, $path, $_GET ?? [], $parsedBody, $headers);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUriPath(): string
    {
        return $this->uriPath;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getParsedBody(): array
    {
        return $this->parsedBody;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    private static function parseBody(array $headers): array
    {
        if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH'])) {
            return [];
        }

        $contentType = $headers['Content-Type'] ?? $headers['content-type'] ?? '';
        $rawBody = file_get_contents('php://input');

        if (stripos($contentType, 'application/json') !== false) {
            $decoded = json_decode($rawBody, true);
            return is_array($decoded) ? $decoded : [];
        }

        return $_POST ?? [];
    }

    private static function fetchHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $name = str_replace('_', '-', substr($key, 5));
                $headers[$name] = $value;
            }
        }

        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }

        return $headers;
    }
}
