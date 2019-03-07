<?php declare(strict_types=1);

namespace Hippiemedia\Agent\Adapter;

use Hippiemedia\Agent\Adapter;
use Hippiemedia\Agent\Resource;
use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Link;
use Hippiemedia\Agent\Operation;

final class SirenJson implements Adapter
{
    public function supports(string $contentType): bool
    {
        return $contentType === 'application/vnd.siren+json';
    }

    public function accepts(): string
    {
        return 'application/vnd.siren+json';
    }

    public function build(Agent $agent, string $url, string $contentType, string $body): Resource
    {
        return $this->buildFromBody($agent, $url, $contentType, json_decode($body));
    }

    private function buildFromBody(Agent $agent, string $url, string $contentType, \stdClass $body)
    {
        $links = array_map(function($link) use($agent) {
            return new Link($agent, current($link->rel), $link->href, null, $link->title ?: '');
        }, $body->links);

        $operations = array_map(function($operation) use($agent) {
            return new Operation($agent, $operation->name, $operation->method, $operation->href, $operation->type, $operation->fields, $operation->title ?: '');
        }, $body->actions);

        return new Resource($url, $links, $operations, json_encode($body->properties));
    }
}
