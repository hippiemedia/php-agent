<?php declare(strict_types=1);

namespace Hippiemedia\Agent\Adapter;

use function Amp\call;
use Hippiemedia\Agent\Adapter;
use Hippiemedia\Agent\Resource;
use Amp\Success;
use Amp\Artax\Response;
use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Link;

final class HalForms implements Adapter
{
    public function supports(string $contentType): bool
    {
        return $contentType === 'application/prs.hal-forms+json';
    }

    public function accepts(): string
    {
        return 'application/prs.hal-forms+json';
    }

    public function build(Agent $agent, Response $response, string $contentType)
    {
        return call(function() use($response, $agent) {
            $body = yield $response->getBody();
            $this->state = json_decode($body, true);
            $links = array_map(function($link) use($agent) {
                return new Link($agent, $link);
            }, $this->state['_links'] ?? []);
            $operations = [];
            return new Resource($links, $operations, $body);
        });
    }
}
