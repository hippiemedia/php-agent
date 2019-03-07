<?php declare(strict_types=1);

require(__DIR__.'/../vendor/autoload.php');

use Amp\Loop;
use Amp\Socket\ClientTlsContext;
use Amp\Artax\DefaultClient;
use Amp\Artax\Request;
use Amp\Artax\FormBody;
use function Amp\GreenThread\{await,coroutine};

$api = require(__DIR__.'/api.php');
$ampClient = new DefaultClient(null, null, (new ClientTlsContext)->withoutPeerVerification());

Loop::run(coroutine(function() use($api, $ampClient) {
    $api(function($method, $uri, array $params = [], $headers = []) use($ampClient) {
        $body = new FormBody();
        $body->addFields($params);

        $response = await($ampClient->request((new Request($uri, $method))
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
    });
}));
