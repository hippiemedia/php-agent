<?php declare(strict_types=1);

use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Adapter\HalJson;
use Hippiemedia\Agent\Adapter\HalForms;
use Amp\Socket\ClientTlsContext;
use Amp\Artax\DefaultClient;
use Amp\Artax\Request;

require(__DIR__.'/../vendor/autoload.php');

\Amp\Loop::run(function() {
    $host = getenv('HOST');
    $client = new DefaultClient(null, null, (new ClientTlsContext)->withoutPeerVerification());
    $client = function($method, $uri, $body = null) use($client, $host) {
        if (is_null(parse_url($uri, PHP_URL_HOST))) {
            $uri = ltrim($uri, '/');
            $uri = "$host/$uri";
        }
        return $client->request((new Request($uri, $method))
            ->withHeader('Authorization', getenv('TOKEN'))
            ->withBody($body)
        );
    };
    $agent = new Agent($client, new HalJson, new HalForms);
    $resource = yield $agent->follow("$host/api");
    echo $resource;

    $resource2 = yield $resource->link('subscribe')->follow();

    echo $resource2;
});

