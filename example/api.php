<?php declare(strict_types=1);

require(__DIR__.'/../vendor/autoload.php');

use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Adapter\HalJson;
use Hippiemedia\Agent\Adapter\HalForms;
use Hippiemedia\Agent\Adapter\SirenJson;

return function($client) use($argv) {
    $agent = (new Agent($client, ['authorization' => getenv('TOKEN')], new HalJson(new HalForms), new HalForms, new SirenJson))
        ->preferring($argv[1] ?? 'application/vnd.siren+json');

    $host = getenv('HOST');
    $entrypoint = $agent->follow("$host/api");
    echo $entrypoint;

    echo $entrypoint->operation('subscribe')->submit(['0[upc]' => 'test']);
};
