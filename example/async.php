<?php declare(strict_types=1);

require(__DIR__.'/../vendor/autoload.php');
require(__DIR__.'/functions.php');

use Amp\Loop;
use Amp\Artax\DefaultClient;
use Hippiemedia\Agent\Client\Async\Amp\Artax;
use Amp\Socket\ClientTlsContext;
use function amp\fiber\coroutine;

Loop::run(coroutine(function() use($argv) {
    $api = require(__DIR__.'/api.php');
    $api(new Artax(new DefaultClient(null, null, (new ClientTlsContext)->withoutPeerVerification())));
}));
