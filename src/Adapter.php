<?php declare(strict_types=1);

namespace Hippiemedia\Agent;

use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Resource;
use Hippiemedia\Agent\Client\Response;

interface Adapter
{
    public function build(Agent $agent, string $url, Response $response): Resource;
}
