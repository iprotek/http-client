=== README.md ===
# Enhanced Guzzle (PHP 8.2)


A modern, enhanced HTTP client inspired by Guzzle, optimized for **PHP 8.2**.


## Features
- ✅ HTTP/2 support by default (via cURL)
- ✅ Immutable `Response` with `readonly`
- ✅ `HttpMethod` enum instead of plain strings
- ✅ Retry middleware with exponential backoff
- ✅ Logging middleware (PSR-3)
- ✅ Async-style `Promise`


## Example
```php
use EnhancedGuzzle\Client;
use EnhancedGuzzle\HttpMethod;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


$logger = new Logger('http');
$logger->pushHandler(new StreamHandler('php://stdout'));


$client = new Client([
'base_uri' => 'https://httpbin.org',
'logger' => $logger,
]);


$res = $client->getJson('/get', ['query' => ['q' => 'hello']]);
print_r($res);


$promise = $client->requestAsync(HttpMethod::GET, '/delay/1');
$promise->then(fn($res) => print_r($res->getStatusCode()));
$promise->wait();
```