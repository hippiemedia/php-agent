<?php declare(strict_types=1);

use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Adapter\HalJson;
use Hippiemedia\Agent\Adapter\HalForms;
use Amp\Loop;
use Amp\Socket\ClientTlsContext;
use Amp\Artax\DefaultClient;
use Amp\Artax\Request;
use Amp\Artax\FormBody;
use function Amp\GreenThread\{await,coroutine};

require(__DIR__.'/../vendor/autoload.php');

Loop::run(coroutine(function() {
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
}));

function agent(string $host, string $auth = null) {
    $ampClient = new DefaultClient(null, null, (new ClientTlsContext)->withoutPeerVerification());
    $client = function($method, $uri, array $params = []) use($ampClient, $host, $auth) {
        if (is_null(parse_url($uri, PHP_URL_HOST))) {
            $uri = ltrim($uri, '/');
            $uri = "$host/$uri";
        }
        $body = new FormBody();
        $body->addFields($params);

        $response = await($ampClient->request((new Request($uri, $method))
            ->withHeader('Authorization', $auth)
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
    return new Agent($client, new HalJson, new HalForms);
}

