<?php declare(strict_types=1);

namespace Hippiemedia\Agent;

use Hippiemedia\Agent\Resource;

final class Agent
{
    private $client;
    private $adapters = [];

    public function __construct(callable $client, Adapter ...$adapters)
    {
        $this->client = $client;
        $this->adapters = $adapters;
    }

    public function follow(string $url): Resource
    {
        return $this->call('GET', $url, [], []);
    }

    public function call(string $method, string $url, array $params, array $headers = []): Resource
    {
        $response = ($this->client)($method, $url, $params, $headers);
        return $this->build($url, $response->getHeader('Content-Type'), $response->getBody());
    }

    public function build(string $url, string $contentType, string $body): Resource
    {
        $adapter = $this->getAdapter($contentType);

        return $adapter->build($this, $url, $contentType, $body, $this->accept($contentType));
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
