<?php declare(strict_types=1);

require(__DIR__.'/../vendor/autoload.php');

use Hippiemedia\Agent\Client\Async\ConcurrentPhp;
use Concurrent\Http\HttpClient;
use Concurrent\Http\HttpClientConfig;
use Nyholm\Psr7\Factory\Psr17Factory;
use Concurrent\Http\Http2\Http2Connector;
use Concurrent\Http\TcpConnectionManager;
use Concurrent\Http\TcpConnectionManagerConfig;
use Concurrent\Network\TlsClientEncryption;

$api = require(__DIR__.'/api.php');

$factory = new Psr17Factory();

$config = new TcpConnectionManagerConfig();
$config = $config->withCustomEncryption('localhost', function (TlsClientEncryption $tls): TlsClientEncryption {
    return $tls->withAllowSelfSigned(true);
});
$manager = new TcpConnectionManager($config);

$config = new HttpClientConfig($factory);
$config = $config->withConnectionManager($manager);
$config = $config->withHttp2Connector(new Http2Connector());

$api(new ConcurrentPhp(new HttpClient($config), $factory));

