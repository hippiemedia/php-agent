<?php declare(strict_types=1);

use Hippiemedia\Agent\Client;
use Hippiemedia\Agent\Client\Response;
use Hippiemedia\Agent\Client\Sync\FileGetContents;

require(__DIR__.'/../vendor/autoload.php');

$api = require(__DIR__.'/api.php');

$api(new FileGetContents([
    'ssl' => ['allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name' => false],
]));

