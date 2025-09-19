<?php
namespace iProtek\HttpClient;


final class Response
{
    public function __construct(
    public readonly int $status,
    public readonly string $rawHeaders,
    public readonly string $body
    ) {}


    public function getStatusCode(): int
    {
        return $this->status;
    }


    public function getBody(): string
    {
        return $this->body;
    }


    public function getHeaders(): array
    {
        $lines = preg_split('/\r?\n/', trim($this->rawHeaders));
        $headers = [];
        foreach ($lines as $line) {
        if (str_contains($line, ':')) {
        [$k, $v] = explode(':', $line, 2);
        $headers[trim($k)] = trim($v);
        }
        }
        return $headers;
    }


    public function toArray(): array
    {
        try {
            return json_decode($this->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return ['raw' => $this->body];
        }
    }
}