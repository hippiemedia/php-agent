<?php declare(strict_types=1);

require(__DIR__.'/../vendor/autoload.php');
require(__DIR__.'/functions.php');

use Amp\Loop;
use Amp\Socket\ClientTlsContext;
use Amp\Artax\DefaultClient;
use Amp\Artax\Request;
use Amp\Artax\StringBody;
use function amp\fiber\{await,coroutine};
use Hippiemedia\Agent\Client;
use Hippiemedia\Agent\Client\Response;

Loop::run(coroutine(function() use($argv) {
    $api = require(__DIR__.'/api.php');
    $ampClient = new DefaultClient(null, null, (new ClientTlsContext)->withoutPeerVerification());

    $api(new class($ampClient) implements Client {
        public function __construct($ampClient) { $this->ampClient = $ampClient; }

        public function __invoke($method, $uri, array $params = [], $headers = []): Response
        {
            $response = await($this->ampClient->request((new Request($uri, $method))
                ->withHeaders($headers)
                ->withBody(new StringBody(http_build_query($params)))
            ));

            return new class($response) implements Response {
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
        }
    });
}));
