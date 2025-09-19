<?php
namespace iProtek\HttpClient\Handler;


use iProtek\HttpClient\Response;


final class CurlHandler
{
public function __construct(private array $config = []) {}


    public function __invoke(array $request): Response
    {
        $method = $request['method'];
        $uri = $request['uri'];
        $options = $request['options'];


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method->value);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout'] ?? $this->config['timeout'] ?? 10);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, $this->config['http_version'] ?? CURL_HTTP_VERSION_2_0);


        $headers = [];
        foreach ($options['headers'] ?? [] as $k => $v) {
            $headers[] = $k . ': ' . $v;
        }


        if (!empty($options['json'])) {
            $body = json_encode($options['json'], JSON_THROW_ON_ERROR);
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        } elseif (!empty($options['body'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $options['body']);
        }

        if ($headers !== []) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }


        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);


        if ($errno) {
            throw new \RuntimeException("cURL error: {$error}");
        }

        $headerSize = $info['header_size'] ?? 0;
        $headerStr = substr($raw, 0, $headerSize);
        $body = substr($raw, $headerSize);
        $status = $info['http_code'] ?? 0;


        return new Response($status, $headerStr, $body);
    }
}