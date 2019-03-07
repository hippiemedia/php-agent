<?php declare(strict_types=1);

require(__DIR__.'/../vendor/autoload.php');

use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Adapter\HalJson;
use Hippiemedia\Agent\Adapter\HalForms;
use Hippiemedia\Agent\Adapter\SirenJson;

return function($client, string $accept = 'application/vnd.siren+json') {
    $agent = new Agent($client, new HalJson, new HalForms, new SirenJson);

    echo "follow /api\n";
    $resource = $agent->follow('/api', [], ['accept' => $accept]);
    echo $resource;

    //echo "follow link 'subscribe'\n";
    //$resource2 = $resource->link('subscribe')->follow();
    //echo $resource2;

    echo "submit operation\n";
    $resource3 = $resource->operation('subscribe')->submit(['0[upc]' => 'test']);
    echo $resource3;
};
