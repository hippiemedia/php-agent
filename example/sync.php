<?php declare(strict_types=1);

require(__DIR__.'/../vendor/autoload.php');

use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Adapter\HalJson;
use Hippiemedia\Agent\Adapter\HalForms;

$api = require(__DIR__.'/api.php');

$api(client(getenv('HOST'), getenv('TOKEN')), $argv[1] ?? 'application/vnd.siren+json');

function client(string $host, string $auth = null) {
    return function($method, $uri, array $params, array $headers) use($host, $auth) {
        if (is_null(parse_url($uri, PHP_URL_HOST))) {
            $uri = ltrim($uri, '/');
            $uri = "$host/$uri";
        }

        $body = file_get_contents($uri, false, stream_context_create([
            'http' => [
                'method' => $method,
                'header' => array_merge(
                    ['Authorization: '.$auth, 'Content-Type: application/x-www-form-urlencoded'],
                    array_map(function($key, $value) {
                        return "$key: $value";
                    }, array_keys($headers), $headers)
                ),
                'content' => http_build_query($params),
            ],
            'ssl' => [
                'allow_self_signed' => true,
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]));

        return new class($body, $http_response_header) {
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
    };
}

