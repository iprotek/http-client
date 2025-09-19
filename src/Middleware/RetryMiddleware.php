<?php
namespace iProtek\HttpClient\Middleware;


final class RetryMiddleware
{
    public function __construct(private readonly int $maxRetries = 3) {}


    public function __invoke(callable $handler): callable
    {
        return function (array $request) use ($handler) {
            $attempt = 0;


            retry:
            try {
                
                $attempt++;
                $response = $handler($request);

                if ($response->getStatusCode() >= 500 && $attempt <= $this->maxRetries) {
                    usleep($this->getBackoffDelay($attempt));
                    goto retry;
                }
                return $response;

            } catch (\Throwable $e) {
                if ($attempt <= $this->maxRetries) {
                    usleep($this->getBackoffDelay($attempt));
                    goto retry;
                }
            throw $e;
            }
        };
    }

    private function getBackoffDelay(int $attempt): int
    {
        return (int)(1000000 * (2 ** ($attempt - 1)));
    }
}