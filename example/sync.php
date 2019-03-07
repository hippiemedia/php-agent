<?php declare(strict_types=1);

use Hippiemedia\Agent\Client;
use Hippiemedia\Agent\Client\Response;

require(__DIR__.'/../vendor/autoload.php');

$api = require(__DIR__.'/api.php');

$api(new class implements Client { function __invoke($method, $uri, array $params = [], array $headers = []): Response {
    $body = file_get_contents($uri, false, stream_context_create($options = [
        'http' => [
            'method' => $method,
            'header' => array_map(function($key, $value) {
                return "$key: $value";
            }, array_keys($headers), $headers),
            'content' => http_build_query($params),
        ],
        'ssl' => ['allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name' => false],
    ]));
    getenv('DEBUG') === '1' && var_dump($options, $body);

    return new class($body, $http_response_header) implements Response {
        public function __construct($body, array $headers)
        {
            $this->body = $body;
            array_shift($headers);
            $this->headers = array_reduce($headers, function($carry, $item) {
                if (!strpos($item, ':')) return ;
                [$key, $value] = explode(': ', $item);
                $carry[$key] = $value;
                return $carry;
            }, []);
        }

        public function getHeader(string $header): ?string
        {
            return $this->headers[$header] ?? '';
        }

        public function getBody(): string
        {
            return $this->body;
        }
    };
}});

