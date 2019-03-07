<?php declare(strict_types=1);

namespace Hippiemedia\Agent;

use Hippiemedia\Agent\Client\Response;

interface Client
{
    public function __invoke($method, $uri, array $params = [], array $headers = []): Response;
}
