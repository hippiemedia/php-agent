<?php declare(strict_types=1);

namespace Hippiemedia\Agent;

final class Resource
{
    public $links;
    public $operations;
    public $body;

    public function __construct(array $links, array $operations, string $body)
    {
        $this->links = $links;
        $this->operations = $operations;
        $this->body = $body;
    }
}
