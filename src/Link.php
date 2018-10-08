<?php declare(strict_types=1);

namespace Hippiemedia\Agent;

use Amp\Promise;

final class Link
{
    private $agent;
    private $link;

    public function __construct(Agent $agent, $link)
    {
        $this->agent = $agent;
        $this->link = $link;
    }

    public function follow(): Promise//<Resource>
    {
        return $this->agent->follow($this->link['href']);
    }
}
