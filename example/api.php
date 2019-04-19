<?php declare(strict_types=1);

require(__DIR__.'/../vendor/autoload.php');

use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Adapter\HalJson;
use Hippiemedia\Agent\Adapter\HalForms;
use Hippiemedia\Agent\Adapter\SirenJson;
use Hippiemedia\Agent\Adapter\Fallback;
use Hippiemedia\Agent\Client\Body;
use Concurrent\Task;
use function Concurrent\all;

return function($client) use($argv) {
    $agent = (new Agent($client, ['authorization' => getenv('TOKEN')], new HalJson(new HalForms), new HalForms, new SirenJson, new Fallback))
        ->preferring($argv[1] ?? 'application/vnd.siren+json');

    $host = getenv('HOST');
    $entrypoint = $agent->follow("$host/api/");

    $coll = $entrypoint->link('updated-since')->follow(['since' => 'yesterday', 'limit' => 9]);

    $subs = Task::await(all(array_map(function ($link) {
        return Task::async(function () use($link) {
            return $link->follow([], true);
        });
    }, $coll->links('subscription'))));

    var_dump(array_map(function($r)  {
        return strval($r->response->body());
    }, $subs));

    //echo $entrypoint->operation('subscribe')->submit(Body::http_build_query(['asin' => rand()]));
};
