<?php declare(strict_types=1);

use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Adapter\HalJson;
use Hippiemedia\Agent\Adapter\HalForms;

require(__DIR__.'/../vendor/autoload.php');

(function() {
    $agent = agent(getenv('HOST'), getenv('TOKEN'));

    echo "follow /api\n";
    $resource = $agent->follow('/api');
    echo $resource;

    echo "follow link 'subscribe'\n";
    $resource2 = $resource->link('subscribe')->follow();
    echo $resource2;

    echo "submit operation\n";
    $resource3 = $resource2->operations[0]->submit(['0[upc]' => 'test']);
    echo $resource3;
})();

function agent(string $host, string $auth = null) {
    $client = function($method, $uri, array $params, array $headers) use($host, $auth) {
        if (is_null(parse_url($uri, PHP_URL_HOST))) {
            $uri = ltrim($uri, '/');
            $uri = "$host/$uri";
        }

        $body = file_get_contents($uri, false, stream_context_create([
            'http' => [
                'method' => $method,
                'header' => array_merge(['Authorization: '.$auth, 'Content-Type: application/x-www-form-urlencoded'], $headers),
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
                    [$key, $value] = explode(': ', $item);
                    $carry[$key] = $value;
                    return $carry;
                }, []);
            }

            public function getHeader(string $header): ?string
            {
                var_dump($this->headers);
                return $this->headers[$header] ?? '';
            }

            public function getBody(): string
            {
                return $this->body;
            }
        };
    };
    return new Agent($client, new HalJson, new HalForms);
}
