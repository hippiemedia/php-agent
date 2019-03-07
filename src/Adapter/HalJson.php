<?php declare(strict_types=1);

namespace Hippiemedia\Agent\Adapter;

use Hippiemedia\Agent\Adapter;
use Hippiemedia\Agent\Resource;
use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Link;
use Hippiemedia\Agent\Operation;

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
        $all = iterator_to_array($this->buildLinksAndOperations($agent, (array)($body->_links ?? []), (array)($body->_embedded ?? []), $contentType));
        $links = array_values(array_filter($all, function($item) {
            return $item instanceof Link;
        }));
        $operations = array_values(array_filter($all, function($item) {
            return $item instanceof Operation;
        }));

        return new Resource($url, $links, $operations, json_encode($body));
    }

    private function buildLinksAndOperations($agent, array $allLinks, array $allEmbedded, $contentType)
    {
        foreach ($allLinks as $rel => $links) {
            $embedded = $this->ensureArray($allEmbedded[$rel] ?? []);
            foreach ($this->ensureArray($links) as $link) {
                $item = $this->findEmbedded($agent, $link->type ?? $contentType, $link->href, $embedded);
                if ($item && count($item->operations) === 1) {
                    $operation = $item->operations[0];
                    yield new Operation($agent, $rel, $operation->method, $operation->href, $operation->contentType, $operation->fields, $operation->title);
                } else {
                    yield new Link($agent, $rel, $link->href, $item, $link->title ?: '');
                }
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
