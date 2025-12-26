<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

class Response
{
    private int $statusCode;
    private array $headers;
    private string $body;

    public function __construct(string $body = '', int $statusCode = 200, array $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->body = $body;
    }

    public static function json(array $data, int $statusCode = 200): self
    {
        return new self(json_encode($data, JSON_UNESCAPED_UNICODE), $statusCode, ['Content-Type' => 'application/json']);
    }

    public static function text(string $text, int $statusCode = 200): self
    {
        return new self($text, $statusCode, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    public static function html(string $html, int $statusCode = 200): self
    {
        return new self($html, $statusCode, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public static function redirect(string $url, int $statusCode = 302): self
    {
        return new self('', $statusCode, ['Location' => $url]);
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header("{$name} : {$value}");
        }
        echo $this->body;
    }
}
