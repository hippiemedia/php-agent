<?php declare(strict_types=1);

namespace Hippiemedia\Agent\Adapter;

use function Hippiemedia\Agent\Url\resolve;
use Hippiemedia\Agent\Adapter;
use Hippiemedia\Agent\Resource;
use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Link;
use Hippiemedia\Agent\Operation;
use Hippiemedia\Agent\Client\Body;
use Hippiemedia\Agent\Client\Response;

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

    public function build(Agent $agent, string $url, Response $response): Resource
    {
        $state = json_decode(strval($response->body()));
        $links = array_map(function($link) use($agent, $url) {
            return new Link($agent, current($link->rel), resolve($url, $link->href), null, $link->title ?: '');
        }, $state->links);

        $operations = array_map(function($operation) use($agent, $url) {
            return new Operation($agent, $operation->name, $operation->method, resolve($url, $operation->href), $operation->type, $operation->fields, $operation->title ?: '');
        }, $state->actions);

        return new Resource($url, $links, $operations, $response);
    }
}
