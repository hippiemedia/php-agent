<?php declare(strict_types=1);

namespace Hippiemedia\Agent\Client\Async;

use Hippiemedia\Agent\Client;
use Hippiemedia\Agent\Client\Response;
use Psr\Http\Message\RequestFactoryInterface;
use Concurrent\Http\HttpClient;
use Hippiemedia\Agent\Client\Body;
use Nyholm\Psr7\Stream;

final class ConcurrentPhp implements Client
{
    private $client;
    private $factory;

    public function __construct(HttpClient $client, RequestFactoryInterface $factory)
    {
        $this->client = $client;
        $this->factory = $factory;
    }

    public function __invoke(string $method, string $url, Body $body = null, array $headers = []): Response
    {
        $request = $this->factory->createRequest($method, $url)->withBody(Stream::create(strval($body)));
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }
        $response = $this->client->sendRequest($request);

        return new class($response) implements Response {
            private $response;
            private $body;
            public function __construct($response)
            {
                $this->response = $response;
            }

            public function statusCode(): int
            {
                return $this->response->getStatusCode();
            }

            public function getHeader(string $header): ?string
            {
                return $this->response->getHeader($header)[0] ?? null;
            }

            public function body(): ?Body
            {
                return $this->body ?: $this->body = Body::fromString(
                    $this->response->getBody()->getContents(),
                    $this->getHeader('content-type')
                );
            }
        };
    }
}
