<?php declare(strict_types=1);

namespace Hippiemedia\Agent;

use Hippiemedia\Agent\Resource;
use Hippiemedia\Agent\Client\Body;
use Hippiemedia\Agent\Client\Response;

final class Agent
{
    private $client;
    private $adapters = [];
    private $defaultHeaders = [];

    public function __construct(callable $client, array $defaultHeaders = [], Adapter ...$adapters)
    {
        $this->client = $client;
        $this->defaultHeaders = $defaultHeaders;
        $this->adapters = $adapters;
    }

    public function follow(string $url, array $headers = []): Resource
    {
        return $this->call('GET', $url, null, $headers);
    }

    public function call(string $method, string $url, Body $body = null, array $headers = []): Resource
    {
        $response = ($this->client)($method, $url, $body, array_merge($this->defaultHeaders, $headers));
        return $this->build($url, $response);
    }

    public function build(string $url, Response $response): Resource
    {
        $contentType = $response->getHeader('content-type');
        $adapter = $this->getAdapter($contentType);

        return $adapter->build($this->preferring($contentType), $url, $response);
    }

    private function getAdapter(string $type): Adapter {
        return current(array_filter($this->adapters, function($adapter) use($type) {
            return $adapter->supports($type);
        }));
    }

    public function preferring(string $contentType): self
    {
        $accept = implode(',', array_map(function($adapter) use($contentType) {
            return $adapter->accepts() . ($adapter->supports($contentType) ? ';q=1' : ';q=0.8');
        }, $this->adapters));

        return new self($this->client, array_merge($this->defaultHeaders, ['accept' => $accept]), ...$this->adapters);
    }
}
