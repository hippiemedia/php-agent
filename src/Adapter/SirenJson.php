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
        $contentType = $response->getHeader('content-type');
        $state = json_decode(strval($response->body()));
        $links = array_map(function($link) use($agent, $url, $contentType, $state) {
            if (is_object($state->entities)) {
                $state->entities = [$state->entities];
            }
            $embedded = $this->findEmbedded($agent, $link->type ?? $contentType, $link->href, $state->entities);
            return new Link($agent, current($link->rel), resolve($url, $link->href), $embedded, $link->title ?: '');
        }, $state->links);

        $operations = array_map(function($operation) use($agent, $url) {
            return new Operation($agent, $operation->name, $operation->method, resolve($url, $operation->href), $operation->type, $operation->fields, $operation->title ?: '');
        }, $state->actions);

        return new Resource($url, $links, $operations, $response);
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
            return count(array_filter($item->links ?? [], function($link) use($href) {
                return in_array('self', $link->rel) && $link->href === $href;
            }));
        }))) ?: null;
    }
}
