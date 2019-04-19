<?php declare(strict_types=1);

require(__DIR__.'/../vendor/autoload.php');

use Hippiemedia\Agent\Client\Async\ConcurrentPhp;
use Concurrent\Http\HttpClient;
use Concurrent\Http\HttpClientConfig;
use Nyholm\Psr7\Factory\Psr17Factory;

$api = require(__DIR__.'/api.php');
$factory = new Psr17Factory();
$api(new ConcurrentPhp(new HttpClient(new HttpClientConfig($factory)), $factory));
