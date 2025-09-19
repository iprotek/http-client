<?php
namespace iProtek\HttpClient;


use iProtek\HttpClient\Handler\CurlHandler;
use iProtek\HttpClient\HandlerStack;
use iProtek\HttpClient\Middleware\RetryMiddleware;
use iProtek\HttpClient\Middleware\LoggingMiddleware;
use Psr\Log\LoggerInterface;


final class Client
{
    private HandlerStack $stack;
    private array $config;


    public function __construct(array $config = [])
    {
    $this->config = array_merge([
        'timeout' => 10,
        'max_retries' => 3,
        'base_uri' => null,
        'logger' => null,
        'http_version' => CURL_HTTP_VERSION_2_0,
    ], $config);


    $this->stack = new HandlerStack(new CurlHandler($this->config));


    // Push default middlewares
    $this->stack->push(new RetryMiddleware($this->config['max_retries']));


    if ($this->config['logger'] instanceof LoggerInterface) {
        $this->stack->push(new LoggingMiddleware($this->config['logger']));
    }
    }

    public function request(HttpMethod $method, string $uri, array $options = []): Response
    {
        $options = array_merge([
            'headers' => [],
            'body' => null,
            'json' => null,
            'query' => []
        ], $options);


        $uri = $this->buildUri($uri, $options['query']);


        $request = [
            'method' => $method,
            'uri' => $uri,
            'options' => $options
        ];

        $handler = $this->stack->resolve();

        return $handler($request);
    }

    public function getJson(string $uri, array $options = []): array
    {
        $res = $this->request(HttpMethod::GET, $uri, $options);
        return $res->toArray();
    }


    public function postJson(string $uri, array $data, array $options = []): array
    {
        $options['json'] = $data;
        $res = $this->request(HttpMethod::POST, $uri, $options);
        return $res->toArray();
    }


    public function requestAsync(HttpMethod $method, string $uri, array $options = []): Promise\Promise
    {
        $promise = new Promise\Promise(function () use (&$promise, $method, $uri, $options) {
        try {
            $res = $this->request($method, $uri, $options);
            $promise->fulfill($res);
        } catch (\Throwable $e) {
            $promise->reject($e);
        }
        });

        return $promise;
    }

    private function buildUri(string $uri, array $query = []): string
    {
        $base = rtrim((string)($this->config['base_uri'] ?? ''), '/');
        $path = ltrim($uri, '/');
        $u = $base ? ($base . '/' . $path) : $path;
        if ($query !== []) {
            $u .= (str_contains($u, '?') ? '&' : '?') . http_build_query($query);
        }
        return $u;
    }
}

