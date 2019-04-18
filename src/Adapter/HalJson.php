<?php declare(strict_types=1);

namespace Hippiemedia\Agent\Adapter;

use function Hippiemedia\Agent\Url\resolve;
use Hippiemedia\Agent\Adapter;
use Hippiemedia\Agent\Adapter\HalForms;
use Hippiemedia\Agent\Resource;
use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Link;
use Hippiemedia\Agent\Operation;
use Hippiemedia\Agent\Client\Body;
use Hippiemedia\Agent\Client\Response;

final class HalJson implements Adapter
{
    private $halForms;

    public function __construct(HalForms $halForms)
    {
        $this->halForms = $halForms;
    }

    public function supports(string $contentType): bool
    {
        return in_array($contentType, ['application/hal+json', 'application/vnd.error+json']);
    }

    public function accepts(): string
    {
        return 'application/hal+json';
    }

    public function build(Agent $agent, string $url, Response $response): Resource
    {
        $contentType = $response->getHeader('content-type');
        $body = json_decode(strval($response->body()));
        $all = iterator_to_array($this->buildLinksAndOperations($agent, $url, (array)($body->_links ?? []), (array)($body->_embedded ?? []), $contentType));
        $links = array_values(array_filter($all, function($item) {
            return $item instanceof Link;
        }));
        $operations = array_values(array_filter($all, function($item) {
            return $item instanceof Operation;
        }));

        return new Resource($url, $links, $operations, $response);
    }

    private function buildLinksAndOperations($agent, $url, array $allLinks, array $allEmbedded, $contentType)
    {
        foreach ($allLinks as $rel => $links) {
            $embedded = $this->ensureArray($allEmbedded[$rel] ?? []);
            foreach ($this->ensureArray($links) as $link) {
                $item = $this->findEmbedded($agent, $link->type ?? $contentType, $link->href, $embedded);
                if ($item && $this->halForms->supports($link->type ?? $contentType)) {
                    $operation = $item->operations[0];
                    yield new Operation($agent, $rel, $operation->method, resolve($url, $operation->href), $operation->contentType, $operation->fields, $operation->title ?? '');
                } else {
                    yield new Link($agent, $rel, resolve($url, $link->href), $item, $link->title ?? '');
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

    private function findEmbedded($agent, string $contentType, string $href, array $embedded)
    {
        return current(array_map(function($item) use($agent, $contentType, $href) {
            $response = new class($contentType, $item) implements Response {
                public function __construct(string $contentType, $item)
                {
                    $this->headers = ['content-type' => $contentType];
                    $this->body = Body::fromString(json_encode($item));
                }
                public function statusCode(): int { return 200; }
                public function getHeader(string $name): ?string { return $this->headers[$name] ?? null; }
                public function body(): ?Body { return $this->body; }
            };
            return $agent->build($href, $response);
        }, array_filter($embedded, function($item) use($href) {
            return $item->_links->self[0]->href ?? null === $href;
        }))) ?: null;
    }
}
