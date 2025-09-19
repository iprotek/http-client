<?php
namespace iProtek\HttpClient\Middleware;


use Psr\Log\LoggerInterface;


final class LoggingMiddleware
{
    public function __construct(private readonly LoggerInterface $logger) {}


    public function __invoke(callable $handler): callable
    {
        return function (array $request) use ($handler) {
            $this->logger->info('http.request', [
                'method' => $request['method']->value,
                'uri' => $request['uri'],
                'options' => $request['options'],
            ]);

            $response = $handler($request);

            $this->logger->info('http.response', [
                'status' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
            ]);


            return $response;
        };
    }
}