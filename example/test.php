<?php declare(strict_types=1);

use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Adapter\JsonHal;
use Hippiemedia\Agent\Adapter\HalForms;
use Amp\Socket\ClientTlsContext;
use Amp\Artax\DefaultClient;
use Amp\Artax\Request;

require(__DIR__.'/../vendor/autoload.php');

\Amp\Loop::run(function() {
    $client = new DefaultClient(null, null, (new ClientTlsContext)->withoutPeerVerification());
    $client = function($uri) use($client) {
        return $client->request((new Request($uri))->withHeader('Authorization', getenv('TOKEN')));
    };
    $agent = new Agent($client, new JsonHal, new HalForms);
    $resource = yield $agent->follow("https://0.0.0.0/api");

    var_dump($resource->body);

    $resource2 = yield $resource->links['subscribe']->follow();

    var_dump($resource2->body);
});

