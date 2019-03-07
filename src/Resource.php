<?php declare(strict_types=1);

namespace Hippiemedia\Agent;

use Hippiemedia\Agent\Link;
use Hippiemedia\Agent\Operation;

final class Resource
{
    public $url;
    public $href;
    public $links;
    public $operations;
    public $body;

    public function __construct(string $url, array $links, array $operations, string $body)
    {
        $this->url = $url;
        $this->links = $links;
        $this->operations = $operations;
        $this->body = $body;
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

        $this->body

        links:
        $links

        operations:
        $operations

        DOC;
    }
}
