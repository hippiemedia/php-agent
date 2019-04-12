<?php declare(strict_types=1);

namespace Hippiemedia\Agent;

use Hippiemedia\Agent\Client\Response;
use Hippiemedia\Agent\Client\Body;

interface Client
{
    public function __invoke($method, $uri, Body $body = null, array $headers = []): Response;
}
