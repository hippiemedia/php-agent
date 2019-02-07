<?php declare(strict_types=1);

namespace Hippiemedia\Agent;

use Amp\Promise;
use Amp\Success;
use Hippiemedia\Agent\Resource;

final class Link
{
    public $rel;
    public $href;
    public $title;
    private $agent;
    private $resolved;

    public function __construct(Agent $agent, string $rel, string $href, Resource $resolved = null, string $title)
    {
        $this->rel = $rel;
        $this->agent = $agent;
        $this->href = $href;
        $this->resolved = $resolved;
        $this->title = $title;
    }

    public function follow(bool $force = false): Promise//<Resource>
    {
        if ($this->resolved && !$force) {
            return new Success($this->resolved);
        }
        return $this->agent->follow($this->href);
    }

    public function __toString(): string
    {
        return <<<DOC
        - GET $this->href
            ($this->rel) $this->title
        DOC;
    }
}
