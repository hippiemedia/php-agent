<?php declare(strict_types=1);

use Amp\Loop;
use Amp\Socket\ClientTlsContext;
use Amp\Artax\DefaultClient;
use Amp\Artax\Request;
use Amp\Artax\FormBody;
use function Amp\GreenThread\{await,coroutine};

require(__DIR__.'/../vendor/autoload.php');
$api = require(__DIR__.'/api.php');

Loop::run(coroutine(function() use($api, $argv) {
    $api(client(getenv('HOST'), getenv('TOKEN')), $argv[1] ?? 'application/vnd.siren+json');
}));

function client(string $host, string $auth = null) {
    $ampClient = new DefaultClient(null, null, (new ClientTlsContext)->withoutPeerVerification());

    return function($method, $uri, array $params, $headers) use($ampClient, $host, $auth) {
        if (is_null(parse_url($uri, PHP_URL_HOST))) {
            $uri = ltrim($uri, '/');
            $uri = "$host/$uri";
        }
        $body = new FormBody();
        $body->addFields($params);

        $response = await($ampClient->request((new Request($uri, $method))
            ->withHeader('Authorization', $auth)
            ->withHeaders($headers)
            ->withBody($body)
        ));

        return new class($response) {
            private $response;
            public function __construct($response)
            {
                $this->response = $response;
            }

            public function getHeader(string $header): ?string
            {
                return $this->response->getHeader($header);
            }

            public function getBody(): string
            {
                return await($this->response->getBody());
            }
        };
    };
}

