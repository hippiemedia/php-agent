<?php declare(strict_types=1);

namespace Hippiemedia\Agent;

use Hippiemedia\Agent\Link;
use Hippiemedia\Agent\Operation;
use Hippiemedia\Agent\Client\Response;

final class Resource
{
    public $url;
    public $href;
    public $links;
    public $operations;
    public $response;

    public function __construct(string $url, array $links, array $operations, Response $response)
    {
        $this->url = $url;
        $this->links = $links;
        $this->operations = $operations;
        $this->response = $response;
    }

    public function link(string $rel): ?Link
    {
        return array_reduce($this->links, function($carry, $link) use($rel) {
            if ($link->rel === $rel) {
                return $link;
            }
            return $carry;
        }, null);
    }

    public function links(string $rel): array
    {
        return array_filter($this->links, function($link) use($rel) {
            if ($link->rel === $rel) {
                return $link;
            }
        });
    }

    public function operation(string $rel): ?Operation
    {
        return array_reduce($this->operations, function($carry, $link) use($rel) {
            if ($link->rel === $rel) {
                return $link;
            }
            return $carry;
        }, null);
    }

    public function __toString(): string
    {
        $links = implode("\n", $this->links);
        $operations = implode("\n", $this->operations);

        return <<<DOC
        $this->url

        body:
        {$this->response->body()}

        links:
        $links

        operations:
        $operations

DOC;
    }
}
