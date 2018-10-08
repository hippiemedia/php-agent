<?php declare(strict_types=1);

namespace Hippiemedia\Agent;

use function Amp\call;
use Amp\Promise;
use Amp\Artax\Response;
use Amp\Success;

final class Agent
{
    private $client;
    private $adapters = [];

    public function __construct(callable $client, Adapter ...$adapters)
    {
        $this->client = $client;
        $this->adapters = $adapters;
    }

    public function follow(string $url): Promise//<Resource>
    {
        return call(function() use($url) {
            $response = yield ($this->client)($url);
            return yield $this->build($response);
        });
    }

    public function build(Response $response)
    {
        $contentType = $response->getHeader('Content-Type');
        $adapter = $this->getAdapter($contentType);

        return $adapter->build($this, $response, $this->accept($contentType));
    }

    private function getAdapter(string $type): Adapter {
        return current(array_filter($this->adapters, function($adapter) use($type) {
            return $adapter->supports($type);
        }));
    }

    private function accept(string $contentType): string
    {
        return implode(',', array_map(function($adapter) use($contentType) {
            return $adapter->accepts() . ($adapter->supports($contentType) ? ';q=1' : ';q=0.8');
        }, $this->adapters));
    }
}
