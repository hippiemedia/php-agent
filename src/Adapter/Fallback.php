<?php declare(strict_types=1);

namespace Hippiemedia\Agent\Adapter;

use Hippiemedia\Agent\Adapter;
use Hippiemedia\Agent\Resource;
use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Link;

final class Fallback implements Adapter
{
    public function supports(string $contentType): bool
    {
        return true;
    }

    public function accepts(): string
    {
        return '*/*';
    }

    public function build(Agent $agent, string $url, string $contentType, string $body): Resource
    {
        return new Resource($url, [], [], $body);
    }
}
