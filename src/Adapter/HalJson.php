<?php declare(strict_types=1);

namespace Hippiemedia\Agent\Adapter;

use Hippiemedia\Agent\Adapter;
use Hippiemedia\Agent\Resource;
use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Link;

final class HalJson implements Adapter
{
    public function supports(string $contentType): bool
    {
        return in_array($contentType, ['application/hal+json', 'application/vnd.error+json']);
    }

    public function accepts(): string
    {
        return 'application/hal+json';
    }

    public function build(Agent $agent, string $url, string $contentType, string $body): Resource
    {
        return $this->buildFromBody($agent, $url, $contentType, json_decode($body));
    }

    private function buildFromBody(Agent $agent, string $url, string $contentType, \stdClass $body)
    {
        $links = $this->buildLinks($agent, (array)($body->_links ?? []), (array)($body->_embedded ?? []), $contentType);

        return new Resource($url, iterator_to_array($links), [], json_encode($body));
    }

    private function buildLinks($agent, array $allLinks, array $allEmbedded, $contentType)
    {
        foreach ($allLinks as $rel => $links) {
            $embedded = $this->ensureArray($allEmbedded[$rel] ?? []);
            foreach ($this->ensureArray($links) as $link) {
                $item = $this->findEmbedded($agent, $link->type ?? $contentType, $link->href, $embedded);
                yield new Link($agent, $rel, $link->href, $item, $link->title ?: '');
            }
        }
    }

    private function ensureArray($items): array
    {
        if (is_array($items)) {
            return $items;
        }
        return array_filter([$items]);
    }

    private function findEmbedded($agent, string $type, string $href, array $embedded)
    {
        return current(array_map(function($item) use($agent, $type, $href) {
            return $agent->build($href, $type, json_encode($item));
        }, array_filter($embedded, function($item) use($href) {
            return $item->_links->self[0]->href ?? null === $href;
        }))) ?: null;
    }
}
