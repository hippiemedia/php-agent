<?php declare(strict_types=1);

use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Adapter\HalJson;
use Hippiemedia\Agent\Adapter\HalForms;
use Amp\Socket\ClientTlsContext;
use Amp\Artax\DefaultClient;
use Amp\Artax\Request;
use Amp\Artax\FormBody;

require(__DIR__.'/../vendor/autoload.php');

\Amp\Loop::run(function() {
    $agent = get_agent();
    $resource = yield $agent->follow("$host/api");
    echo $resource;

    $resource2 = yield $resource->link('subscribe')->follow();
    echo $resource2;

    $resource3 = yield $resource2->operations[0]->submit(['0[upc]' => 'test',]);
    echo $resource3;
});


function get_agent() {
    $host = getenv('HOST');
    $client = new DefaultClient(null, null, (new ClientTlsContext)->withoutPeerVerification());
    $client = function($method, $uri, array $params = []) use($client, $host) {
        if (is_null(parse_url($uri, PHP_URL_HOST))) {
            $uri = ltrim($uri, '/');
            $uri = "$host/$uri";
        }
        $body = new FormBody();
        $body->addFields($params);
        return $client->request((new Request($uri, $method))
            ->withHeader('Authorization', getenv('TOKEN'))
            ->withBody($body)
        );
    };
    return new Agent($client, new HalJson, new HalForms);
}

